<template>
    <div class="chat_box_all" style="height:500px;">
        <div>
            <div id="chat_content" class="chat_sendcontent">
                <ul v-if="chatlog.length > 0">
                    <li v-if="morelog" id="chat_more" class="chat_more" @click="showChat()">
                        <div class="chat_comjobtip"><span class="chat_comjobtip_p">查看更多消息</span></div>
                    </li>
                    <li v-for="(msg, ck) in chatlog" :key="ck" class="chat_li">
                        <div v-if="msg.time" class="chat_sendtime"><span class="chat_sendtime_b">{{msg.time}}</span></div>
                        <!--消息撤回-->
                        <div v-if="msg.msgtype=='recall'" class="chat_recall chatRecalls"><span class="chat_recall_b">{{msg.content}}</span></div>
                        <div v-if="msg.msgtype=='char' || msg.msgtype=='voice' || msg.msgtype=='spview' || msg.msgtype=='map'">
                            <!--我方消息-->
                            <div v-if="msg.mine" class="chat_sendcont">
                                <!--头像-->
                                <div class="chat_sendcont_pic"><img :src="msg.avatar"></div>
                                <!--面试邀请-->
                                <span v-if="msg.inviteid" class="chat_sendcont_p">
									<b class="chat_sendcont_yqbox">发送了面试邀请<i class="chat_sendcont_yq"></i></b>
								</span>
                                <!--语音-->
                                <span v-else-if="msg.msgtype == 'voice'" class="chat_sendcont_p">
									<audioyy :src="msg.voice"></audioyy>
								</span>
                                <!--我发的图片-->
                                <span v-else-if="msg.image" class="chat_sendcont_p">
									<img class="chat_photos" :src="msg.image">
								</span>
                                <div v-else-if="msg.msgtype == 'spview'" class="chat_sendcont_p">
                                    <div class="spqxboxtwo" v-if="msg.content == 'closesp'"><i class="spqx2"></i>{{msg.fusertype != usertype ? '对方' : ''}}已取消</div>
                                    <div class="spqxboxtwo" v-else-if="msg.content == 'refused'"><i class="spqx2"></i>{{msg.fusertype != usertype ? '对方' : ''}}已拒绝</div>
                                    <div class="spqxboxtwo" v-else><i class="spqx2"></i>{{msg.content}}</div>
                                </div>
                                <span v-else-if="msg.msgtype == 'map'" class="chat_sendcont_p"><img class="chat_photos" :src="msg.url"></span>
                                <span v-else class="chat_sendcont_p">
									<span v-for="(cn, cnk) in msg.content" :key="cnk">
										<!--表情-->
										<img v-if="cn.t == 'emoji'" class="chat_bq_img" :src="cn.n">
                                        <!--文字-->
										<span v-else-if="cn.t == 'char'">
											<b>{{cn.n}}</b>
										</span>
									</span>
								</span>
                            </div>
                            <!--对方消息-->
                            <div v-else class="chat_other_say">
                                <!--头像-->
                                <div class="chat_other_pic"><img :src="msg.avatar"></div>
                                <!--面试邀请-->
                                <span v-if="msg.inviteid" class="chat_sendcont_p2">
									<b class="chat_sendcont_jsbox">发送了面试邀请<i class="chat_sendcont_jsyq"></i></b>
								</span>
                                <!--语音-->
                                <span v-else-if="msg.msgtype == 'voice'" class="chat_sendcont_p2">
									<audioyy :src="msg.voice"></audioyy>
								</span>
                                <!--我收到的图片-->
                                <span v-else-if="msg.image" class="chat_sendcont_p2">
									<img class="chat_photos" :src="msg.image">
								</span>
                                <div v-else-if="msg.msgtype == 'spview'" class="chat_sendcont_p2">
                                    <div class="spqxbox" v-if="msg.content == 'closesp'"><i class="spqx"></i>{{msg.fusertype != usertype ? '对方' : ''}}已取消</div>
                                    <div class="spqxbox" v-else-if="msg.content == 'refused'"><i class="spqx"></i>{{msg.fusertype != usertype ? '对方' : ''}}已拒绝</div>
                                    <div class="spqxbox" v-else><i class="spqx"></i>{{msg.content}}</div>
                                </div>
                                <span v-else-if="msg.msgtype == 'map'" class="chat_sendcont_p2"><img class="chat_photos" :src="msg.url"></span>
                                <span v-else class="chat_sendcont_p2">
									<span v-for="(cn, cnk) in msg.content" :key="cnk">
										<!--表情-->
										<img v-if="cn.t == 'emoji'" class="chat_bq_img" :src="cn.n">
                                        <!--文字-->
										<span v-else-if="cn.t == 'char'">
											<b>{{cn.n}}</b>
										</span>
									</span>
								</span>
                            </div>
                        </div>
                        <div v-else-if="msg.msgtype == 'ask'">
                            <div v-if="msg.mine" class="chat_hdh">
                                <span><div class="chat_hdh_p">{{msg.asktext}}</div></span>
                            </div>
                            <div v-else class="chat_hhyq">
                                <div class="chat_hhyq_p chat_hhyq_pq">
                                    <i :class="msg.asktype=='tel'?'chat_hhyq_icon':'chat_hhwx_icon'"></i>{{msg.asktext}}
                                </div>
                                <div v-if="msg.askstatus == '1'" :id="'hhbtn_'+msg.chatid" class="chat_hhyq_bth"><span class="wxyty">已同意</span></div>
                                <div v-if="msg.askstatus == '2'" :id="'hhbtn_'+msg.chatid" class="chat_hhyq_bth"><span class="wxyty">已拒绝</span></div>
                                <div v-else class="chat_hhyq_bth">
                                    <div class="bh_bth bh_bthline">同意</div>
                                    <span class="bh_bth">拒绝</span>
                                </div>
                            </div>
                        </div>
                        <div v-else-if="msg.msgtype == 'confirm' && msg.askvalue" class="chat_hhyq">
                            <div class="chat_hhyq_p"><i :class="msg.confirmtype=='tel'?'chat_hhyq_icon':'chat_hhwx_icon'"></i>{{msg.askmsg}}{{msg.askvalue}}</div>
                            <div class="chat_hhyq_bth"><span class="">{{msg.confirmtype=='wx'?'复制微信号':'复制手机号'}}</span></div>
                        </div>
                        <div v-else-if="msg.msgtype == 'refuse' && msg.refusetext" class="chat_hdh">
                            <span><div class="chat_hdh_p">{{msg.refusetext}}</div></span>
                        </div>
                        <div v-else-if="(msg.msgtype == 'job' || msg.msgtype == 'adminjob') && msg.job" class="chat_yx_job" @click="toShow(msg.job.weburl)">
							<span>
								<div class="chat_yx_job_name">{{msg.job.name}}</div>
								<span class="chat_yx_xz">{{msg.job.job_salary}}</span>
								<div class="chat_yx_com_name">{{msg.job.com_name}}</div>
								<div class="">
									{{msg.job.job_city_one}}{{msg.job.job_city_two?'-'+msg.job.job_city_two:''}}
									{{msg.job.job_exp?'.'+msg.job.job_exp+'经验':''}}
									{{msg.job.job_edu?'.'+msg.job.job_edu+'学历':''}}
								</div>
								<div class="chat_yx_time" v-if="msg.newjob">{{msg.ctime}} {{msg.newjob_tip}}</div>
							</span>
                        </div>
                        <div v-else-if="msg.msgtype == 'resume'" class="chat_userresume" @click="toShow(expect.weburl)">
							<span>
								<div class="chat_userresume_name">{{expect.uname}}<span class="chat_userresume_ys">{{expect.sex_n}},{{expect.age}}岁</span></div>
								<div class="chat_userresume_info">{{expect.exp_n}} . {{expect.edu_n}} </div>
								<div class="chat_userresume_want">期望职位 <span class="chat_userresume_wantjob">{{expect.name}}</span> </div>
								<div class="chat_userresume_pic"><img :src="expect.photo"></div>
							</span>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>
<script>
    window.audioList = [];
    module.exports = {
        props: {
            chatid: {
                type: String,
                default: ''
            },
            from: {
                type: String,
                default: ''
            },
            reportparam: {
                type: Object,
                default: function () {
                    return {};
                }
            }
        },
        data: function () {
            return {
                linkman: '',
                chatpage: 1,
                expect: [],
                joblist: [],
                chatlog: [],
                morelog: false,
            }
        },
        components: {
            'audioyy': httpVueLoader('../../../component/audioyy.vue')
        },
        mounted() {
            this.showChat();
        },
        methods: {
            showChat: function () {
                var that = this;
                var params = {page: this.chatpage};
                var url = '';
                if (that.from == 'report') {
                    params.uid = this.reportparam.uid;
                    params.usertype = this.reportparam.usertype;
                    params.toid = this.reportparam.toid;
                    params.tusertype = this.reportparam.tusertype;
                    url = 'm=yunying&c=report_chat&a=userchat';
                } else {
                    params.id = this.chatid;
                    url = 'm=tool&c=chatlog&a=userchat';
                }
                httpPost(url, params).then(function (result) {
                    var res = result.data.data;
                    if (res) {
                        if (res.msg) {
                            message.error(res.msg);
                            return;
                        }
                        if (res.fuser && res.tuser) {
                            that.linkman = res.tuser.nickname;
                            that.chattitle = res.fuser.nickname + '和' + res.tuser.nickname + '的聊天记录';
                            that.$emit("set-title", {
                                title: that.chattitle
                            });
                        }
                        that.chatpage += 1;
                        var scroll = null;
                        if (that.chatpage > 2) {
                            // 第二页开始，记录前一次滚动距离
                            scroll = document.getElementById('chat_content').scrollHeight;
                        }
                        if (res.code == 0) {
                            var list = [];
                            if (res.joblist) {
                                that.joblist = res.joblist;
                            }
                            var chatlog = res.data;
                            that.expect = res.expect;
                            if (chatlog.length > 0) {

                                pastSend = chatlog[0].sendTime;
                                that.morelog = false;
                                for (var i in chatlog) {
                                    chat = renderPast(chatlog[i], i, that);
                                    if (chat) {
                                        list.push(chat);
                                    }
                                    // 最后一条时，如还有历史消息，增加更多消息
                                    if (i == chatlog.length - 1 && res.ismore) {
                                        that.morelog = true;
                                        lastid = chatlog[i]['id'];
                                    }
                                }
                                console.log(list);
                                list.reverse();
                                that.chatlog = list.concat(that.chatlog);
                                if (scroll) {
                                    that.$nextTick(function () {
                                        // 渲染后，滚动到上一页的距离
                                        var newScroll = document.getElementById('chat_content').scrollHeight;
                                        chatScrollTo(newScroll - scroll);
                                    })
                                }
                            } else {
                                that.morelog = false;
                            }
                        }
                        //    moreReady = true;
                    }
                }).catch(function (e) {
                    console.log(e)
                })
            },
            toShow: function (url) {
                if (url && url != '') {
                    window.open(url);
                }
            }
        }
    };
</script>
<style scoped>
    .moduleSeachmore{padding:0}
    .moduleSeachs{padding:0 0 12px 0;width:100%}
    .tableSeachInptsmalltwo{margin-bottom:0;margin-right:12px}
    .tableSeachInptsmalltwo .el-input__inner{height:32px;line-height:32px;width:260px;padding:0 5px}
    .el-dialog__body{padding:0 20px}
    .pad20{padding:0 20px}
</style>