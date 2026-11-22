<template>
    <div class="moduleChatSubs">
        <div class="guiDontaSubject">
            <div class="guiDontaHeads">
                <div class="guiDontaDays">
                    <span @click="changeTimeType('1')" :class="isyesterday == '1' ? 'ontaDayCur' : ''">昨日</span>
                </div>
                <div class="guiDontaFros">
                    <el-date-picker v-model="time" value-format="yyyy-MM-dd" size="small" placeholder="选择日期">
                    </el-date-picker>
                    <el-button icon="el-icon-search" size="mini" type="primary" @click="changeTimeType('5')">查询</el-button>
                </div>
            </div>
            <div class="guiDontaGuijis">
                <el-empty v-if="emptytext == '暂无数据'" :image-size="200"></el-empty>
                <div v-else class="guiDontaGuilieb">
                    <el-timeline class="timeline">
                        <el-timeline-item v-for="(item,key) in timeData" :key="key" :timestamp="item.time_n" placement="top">
                            <div class="guijibutns">
                                <el-button type="text">{{item.name}}</el-button>
                            </div>
                        </el-timeline-item>
                    </el-timeline>
                    <div class="modulePagNum">
                        <el-pagination background @size-change="handleSizeChange"
                                       @current-change="handleCurrentChange" :current-page="currentPage" :page-sizes="pageSizes"
                                       :page-size="perPage" layout="total, sizes, prev, pager, next, jumper" :total="total"
                                       :pager-count="pagerCount">
                        </el-pagination>
                    </div>
                </div>
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
        searchtime: {
            type: String,
            default: ''
        },
    },
    data: function () {
        return {
            uid: '',
            time: '',
            timeData: [],
            currentPage: 1,
            pagerCount: 5,
            perPage: 0,
            pageSizes: [],
            total: 0,
            emptytext: '暂无数据',
            yesterday: '',
			prevPage: 0
        }
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
        searchtime: {
            handler(value) {
                if (this.time != value) {
                    this.time = value
                }
            },
            deep: true,
            immediate: true
        },
    },
    mounted() {
        const date = new Date();
        this.yesterday = formatDate(new Date(date.getTime() - 24 * 3600 * 1000))
        // this.getList()
    },
    computed: {
        isyesterday: function(){
            if (this.time != '' && this.time == this.yesterday) {
                return 1
            } else {
                return 0
            }
        }
    },
    methods: {
        changeTimeType: function(type){
            this.currentPage = 1
            if (type == 1) {
                this.time = this.yesterday
            }
            this.getList()
        },
        handleSizeChange(val) {
            this.perPage = val;
			scrollToTop('.timeline')
            this.getList()
        },
        handleCurrentChange(val) {
            this.currentPage = val;
            this.getList()
        },
        async getList() {
            let that = this;
            let params = {
                page: that.currentPage,
                pageSize: that.perPage
            }
            if (that.uid) {
                params.uid = that.uid
            }
            if (that.time) {
                params.time = that.time
            }
            that.loading = true;
            that.emptytext = "数据加载中";
            httpPost('m=crm&c=dataBoard&a=linedata', params, { hideloading: true }).then(function (result) {
                var res = result.data
                if (res.error == 0) {
                    that.timeData = res.data.list;
                    if (that.timeData.length === 0) {
                        that.emptytext = "暂无数据";
                    }
                    that.perPage = parseInt(res.data.perPage)
                    that.pageSizes = res.data.pageSizes
                    that.total = parseInt(res.data.total)
					if(that.prevPage != that.currentPage){
                        that.prevPage = that.currentPage;
                        scrollToTop('.timeline')
                    }
                    that.loading = false;
                }
            }).catch(function (e) {
                console.log(e)
            })
        },
    },
};
</script>
<style scoped>
.guiDontaGuilieb{
    overflow: hidden;
    position: relative;
    width: 100%;
    height: 100%;
}
.guiDontaGuilieb .el-timeline{
    overflow-y: auto;
    position: relative;
    width: 100%;
    height: max-content;
    max-height: calc(100% - 40px);
}
</style>