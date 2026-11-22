<template>
    <div class="liveKztVideo">
        <div class="roaUpsLeftVide" id="livebody" v-if="livestatus != '2'" :style="playeredStyle">
            <video v-if="livestatus == '3'" id="live_video" preload="auto" custom-cache="true" playsinline webkit-playsinline></video>
            <div v-if="livestatus == '1'" class="countTimeFlex">
                <div id="countnotice">{{countdownTip}}</div>
                <div id="countdown" v-if="showcountdown">
                    <span>{{day}}</span><b>天</b>
                    <span>{{hour}}</span> <b>时</b>
                    <span>{{minute}}</span> <b>分</b>
                    <span>{{second}}</span> <b>秒</b>
                </div>
            </div>
            <div class="psLeftVideClck" v-if="livestatus == '1'" @click="startLive">
                <el-button type="danger" size="small" round>开始本场直播</el-button>
            </div>
            <div class="psLeftVideClck" v-if="livestatus == '3'" @click="stopLive">
                <el-button type="danger" size="small" round>结束本场直播</el-button>
            </div>
        </div>
        <div v-else>
            <div class="roaUpsLeftVide" id="playered" :style="playeredStyle" v-if="recordurl">
                <div class="psLeftVideClck" @click="backShow">
                    <el-button type="danger" size="small" round>重新生成回放</el-button>
                </div>
                <!--回放-->
            </div>
            <div class="roaUpsLeftVide" v-else>
                <div class="psLeftVideClck" @click="backShow">
                    <el-button type="danger" size="small" round>生成回放</el-button>
                </div>
                <div class="liveKztVidEnd">
                    <span>直播已结束</span>
                </div>
                <!--<div class="liveKztVidHuf" @click="backShow">-->
                    <!--<el-button type="danger" round size="small">生成回放</el-button>-->
                <!--</div>-->
            </div>
        </div>
        <!--生成直播回放-->
        <div class="modluDrawer">
            <el-dialog title="生成直播回放" :visible.sync="drawerbackshow" :with-header="true" append-to-body
                       :modal-append-to-body="false" :show-close="true" width="350px">
                <el-alert type="warning" description="只有60天内有直播记录的才支持生成回放" :closable="false"></el-alert>
                <div style="margin-top: 10px;">
                    开始时间：
                    <el-date-picker style="width: 236px;" v-model="stime" type="datetime" placeholder="选择开始日期"
                                    value-format="yyyy-MM-dd HH:mm">
                    </el-date-picker>
                </div>
                <div style="margin-top: 10px;">
                    结束时间：
                    <el-date-picker style="width: 236px;" v-model="etime" type="datetime" placeholder="选择结束时间"
                                    value-format="yyyy-MM-dd HH:mm">
                    </el-date-picker>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="drawerbackshow = false">取 消</el-button>
                    <el-button type="primary" @click="playback">确 定</el-button>
                </span>
            </el-dialog>
        </div>
    </div>
</template>
<script>
    var socket = window.parent.homeapp.socket, xjhsyncid = null,mbstyle = null
    module.exports = {
        props: {
            xinfo: {
                type: Object,
                default: ''
            },
        },
        data: function () {
            return {
                drawerbackshow: false,
                info: {},
                livestatus: '',
                recordurl: '',
                stime: '',
                etime: '',
                countdownTip: '',
                showcountdown: false,
                day: '0',
                hour: '00',
                minute: '00',
                second: '00',
                player: null,
                playeredStyle: ''
            }
        },
        mounted() {
            var that = this
            that.playeredStyle = 'width:' + parseInt(window.innerWidth * 0.45) + 'px;height:' + parseInt(window.innerHeight * 0.45) + 'px;'
            setTimeout(function(){
                if(that.livestatus == 3){
                    // 直播
                    livePlay(that.info.hls, that.info.live_poster, parseInt(window.innerWidth * 0.45), parseInt(window.innerHeight * 0.42));
                }else if(that.livestatus == 2 && that.recordurl){
                    // 回放
                    that.initPlayer()
                    //playered(that.recordurl, mbstyle);
                }
            }, 200)
            this.receiveSocket();
        },
        watch: {
            xinfo: {
                handler(val, oldVal) {
                    var that = this
                    this.info = val;
                    if (val.mbstyle) {
                        mbstyle = val.mbstyle
                    }
                    if (val.livestatus) {
                        this.livestatus = val.livestatus
                    }
                    if (val.recordurl) {
                        this.recordurl = val.recordurl
                    }
                    if(this.livestatus == 3 && !player){
                        // 直播
                        livePlay(that.info.hls, that.info.live_poster, parseInt(window.innerWidth * 0.45), parseInt(window.innerHeight * 0.42));
                    }else if(this.livestatus == 2 && this.recordurl && !this.palyer){
                        // 回放
                        setTimeout(function(){
                             that.initPlayer()
                        }, 200)
                    }
                    if (val.syncid) {
                        xjhsyncid = val.syncid
                    }
                    if (val.stime) {
                        this.countdown()
                    }
                },
                immediate: true,
                deep: true,
            },
        },
        methods: {

            // 初始化播放器
            initPlayer() {
                if (this.player) {
                    this.player.destroy();
                    this.player = null;
                }
                let options = {
                    m3u8: this.recordurl /* 播放地址 */,
                    live: false /* 是否直播 */,
                    autoplay: true,
                    poster: { style: "cover", src: mbstyle +'/images/qiniuset.png' },
                    pausePosterEnabled: true /* 暂停时显示封面 默认为true */,
                    wording: {
                        1002: "即将直播，请稍等",
                        2032: "请求视频失败，请检查网络",
                        2048: "请求m3u8文件失败，可能是网络错误或者跨域问题"
                    },
                    controls: "system", //default||'' 显示默认控件，none 不显示控件，system 移动端显示系统控件
                    x5_player: true,
                    x5_type: "h5-page",
                    x5_fullscreen: true,
                    listener: function(msg) {
                        // 播放失败，重新连接
                        if (msg.type == "error") {
                            setTimeout(() => {
                                this.player.load(); // 重连
                            }, 4000);
                        }
                    }
                };
                // 初始化
                this.player = new TcPlayer("playered", options);
            },

            // 直播开始倒计时
            countdown: function () {
                var that = this
                var time_r = setInterval(function () {
                    that.docountdown()
                    if (Number(that.hour) === 0 && Number(that.day) === 0 && Number(that.minute) === 0 && Number(that.second) === 0) {
                        clearInterval(time_r)
                    }
                }, 100)
            },
            docountdown: function () {
                // debugger
                let now_time = new Date().getTime()
                if (this.info.stime && this.info.stime * 1000 > now_time) {
                    let lefttime = this.info.stime * 1000 - now_time
                    this.day = Math.floor(lefttime / 1000 / 60 / 60 / 24)
                    let h = Math.floor(lefttime / 1000 / 60 / 60 % 24)
                    this.hour = h < 10 ? '0' + h : h
                    let m = Math.floor(lefttime / 1000 / 60 % 60)
                    this.minute = m < 10 ? '0' + m : m
                    let s = Math.floor(lefttime / 1000 % 60)
                    this.second = s < 10 ? '0' + s : s
                    this.showcountdown = true
                    this.countdownTip = '直播开始倒计时，点击“开始本场直播”后将正式开启本场直播'
                } else {
                    this.showcountdown = false
                    this.day = '0'
                    this.hour = '00'
                    this.minute = '00'
                    this.second = '00'
                    this.countdownTip = '点击“开始本场直播”后将正式开启本场直播'
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
            // 生成直播回放
            backShow: function () {
                this.stime = this.info.stime_m
                this.etime = this.info.etime_m
                this.drawerbackshow = true
            },
            playback: function () {
                var that = this
                if (that.stime == '') {
                    return layer.msg('请选择开始时间', 2, 8);
                } else if (that.etime == '') {
                    return layer.msg('请选择结束时间', 2, 8);
                }
                var params = {id: that.info.id, stime: that.stime, etime: that.etime}
                httpPost('m=neirong&c=xjhlive&a=playback', params).then(function (response) {
                    if (response.data.error == 0) {
                        message.success(response.data.msg, function () {
                            that.$parent.getinfo()
                        })
                    } else {
                        message.error(response.data.msg);
                    }
                }).catch(function (error) {
                    console.log(error);
                })
            },
            startLive: function () {
                var that = this
                that.$confirm('确定要开始本场直播吗？', '温馨提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'info'
                }).then(() => {
                    httpPost('m=neirong&c=xjhlive&a=startlive', {id: that.info.id}).then(function (response) {
                        let res = response.data;
                        if (res.error == 0) {
                            message.success(res.msg, function () {
                                that.$parent.getinfo()
                                that.livestatus = 3
                                var msg = {
                                    type: 'startLive',
                                    data: {
                                        to: {xjhid: xjhsyncid}
                                    }
                                };
                                that.sendsocketmsg(msg)
                                setTimeout(function () {
                                    livePlay(that.info.hls, that.info.live_poster);
                                }, 100);
                            });
                        } else {
                            message.error(res.msg);
                        }
                    }).catch(function (error) {
                        console.log(error);
                    })
                }).catch(() => {
                });
            },
            stopLive: function () {
                var that = this
                that.$confirm('确定要结束本场直播吗？', '温馨提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'info'
                }).then(() => {
                    httpPost('m=neirong&c=xjhlive&a=liveEnd', {id: that.info.id}).then(function (response) {
                        let res = response.data;
                        if (res.error == 0) {
                            message.success(res.msg, function () {
                                that.$parent.getinfo()
                                that.livestatus = 2
                                var msg = {
                                    type: 'stopLive',
                                    data: {
                                        to: {xjhid: xjhsyncid}
                                    }
                                };
                                that.sendsocketmsg(msg)
                                setTimeout(function () {
                                    livePlay(that.info.hls, that.info.live_poster);
                                }, 100);
                            });
                        } else {
                            message.error(res.msg);
                        }
                    }).catch(function (error) {
                        console.log(error);
                    })
                }).catch(() => {
                });
            },
            receiveSocket:function(){
				window.parent.homeapp.receiveMessage(this.getMessage);
			},
			getMessage: function(e){
				if(typeof(xjhsyncid) != "undefined" && xjhsyncid != "" && xjhsyncid == e.data.xjhid){
					if(e.type == 'startLive'){
						this.livestatus = 3;
						livePlay();
					}else if(e.type == 'stopLive'){
						if(player){
							player.dispose();
							player = null;
						}
						this.livestatus = 2;
					}
				}
			}
        },
    };
</script>

<style scoped>
    .psLeftVideClck {
        overflow: hidden;
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 33;
    }

    #countnotice {
        color: #fff;
    }

    #countdown {
        overflow: hidden;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 10px;
    }

    #countdown span{
        overflow: hidden;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 25px;
        height: 25px;
        background: #fff;
        border-radius: 3px;
        font-size: 14px;
        color: #2778F8;
        margin: 0 5px;
        font-weight: 600;
    }
    #countdown b{
        overflow: hidden;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 25px;
        height: 25px;
        background: #fff;
        border-radius: 3px;
        font-size: 14px;
        color: #333;
        font-weight: 500;
        margin: 0 5px;
    }
</style>