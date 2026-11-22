<template>
    <div class="liveKztMokuai">
        <div class="liveKztComHead">
            <div class="liveKztComTite">
                <span>招聘企业</span>
            </div>
            <div class="liveKztComSeach">
                <el-input v-model="keyword" clearable placeholder="请输入内容" size="mini">
                    <el-button slot="append" icon="el-icon-search" @click="comSearch"></el-button>
                </el-input>
            </div>
        </div>
        <div class="liveKztComZhiw">
            <el-carousel height="100%" :autoplay="false">
                <el-carousel-item  v-for="(coms, index) in comlist" :key="'coms'+index">
                    <div class="liveKztComGund">
                        <div class="liveKztComItem" v-for="(com, cindex) in coms" :key="'com'+cindex">
                            <div class="liveKztComConts">
                                <div class="liveKztComImg">
                                    <el-image :src="com.logo" style="width: 54px; height: 54px"></el-image>
                                </div>
                                <div class="liveKztComName">
                                    <span>{{com.comname}}</span>
                                </div>
                                <div class="liveKztComData">
                                    <div class="liveKztDatNum">
                                        <div>
                                            <b>{{com.jobcount}}</b>
                                            <span>在招职位</span>
                                        </div>
                                    </div>
                                    <div class="liveKztDatSuxs"></div>
                                    <div class="liveKztDatNum">
                                        <div>
                                            <b>{{com.ltime}}分钟</b>
                                            <span>讲解时长</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="liveKztComStar">
                                    <el-button @click="setExplain(index, cindex)" type="primary" size="mini" :style="com.status == '1' || com.status == '3' ? 'cursor:not-allowed' : ''">开始讲解</el-button>
                                </div>
                            </div>
                            <div class="liveKztZuata jjz" v-if="com.status == '1'">
                                <span>讲解中</span>
                            </div>
                            <div class="liveKztZuata" v-else-if="com.status == '2'">
                                <span >待讲解</span>
                            </div>
                            <div class="liveKztZuata yjj" v-else-if="com.status == '3'">
                                <span>已讲解</span>
                            </div>
                        </div>
                    </div>
                </el-carousel-item>
            </el-carousel>
        </div>
    </div>
</template>

<script>
var socket = window.parent.homeapp.socket;
module.exports = {
    props: {
        id: {
            type: String,
            default: ''
        },
        list: {
            type: Array,
            default: () => []
        },
    },
    data: function () {
        return {
            xid: '',
            comlist: [],
            keyword: '',
            url: 'https://fuss10.elemecdn.com/e/5d/4a731a90594a4af544c0c25941171jpeg.jpeg',
        }
    },
    mounted() {

    },
    watch: {
        id: {
            handler(val, oldVal) {
                this.xid = val;
            },
            immediate: true,
            deep: true,
        },
        list: {
            handler(val, oldVal) {
                this.comlist = val;
            },
            immediate: true,
            deep: true,
        },
    },
    methods: {
        comSearch: function(){
            var that = this;
            if(that.keyword == ''){
                message.error('请输入关键词')
                that.$parent.getControlData();
                return false
            }
            httpPost('m=neirong&c=xjhlive&a=getControlCom', {xid: that.xid, keyword: that.keyword}).then(function (response) {
                var res = response.data
                if (res.error == 0) {
                    that.comlist = res.data;
                } else {
                    message.error(res.msg);
                }
            }).catch(function (error) {
                console.log(error);
            })
        },
        setExplain: function(key, ckey){
            var that = this;
            var com = this.comlist[key][ckey];
            if(com.status == 2){
                httpPost('m=neirong&c=xjhlive&a=setExplain', {id: com.id, xid: that.xid}).then(function (response) {
                    var res = response.data
                    if (res.error == 0) {
                        that.$parent.getControlData();
                        var msg = {
                            type: 'xjhNowcom',
                            data: {
                                to: {xjhid: response.data.data.xjhsyncid, uid: com.id}
                            }
                        };
                        that.sendsocketmsg(msg)
                    } else {
                        message.error(res.msg);
                    }
                }).catch(function (error) {
                    console.log(error);
                })
            }
        },
        sendsocketmsg(msg) {
            if (socket && socket.readyState == 1) {
                socket.send(JSON.stringify(msg));
            } else {
                window.parent.homeapp.websocket()
                var timer = setInterval(function () {
                    if (window.parent.homeapp.socket && window.parent.homeapp.socket.readyState == 1) {
                        socket = window.parent.homeapp.socket
                        clearInterval(timer);
                        socket.send(JSON.stringify(msg));
                    }
                }, 100);
                setTimeout(function () {
                    if (timer) {
                        clearInterval(timer);
                    }
                }, 10000);
            }
        },
    },
};
</script>

<style scoped>

</style>