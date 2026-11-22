<template>
    <div class="liveKztMokuai">
        <div class="liveKztsHudong">
            <el-tabs v-model="activeName" type="card">
                <el-tab-pane label="聊天互动" name="hudong">
                    <div class="liveKztChatCont" id="chat_content">
                        <div class="loadingMore" @click="loadingMore" v-if="ismore">
                            <span>加载更多</span>
                        </div>
                        <template v-for="(item, lkey) in chatlist">
                            <template v-if="item.msgtype == 'char'">
                                <!-- 时间线 -->
                                <div class="liveKztTime" :key="'sjx'+lkey" v-if="item.time">
                                    <span>{{ item.time }}</span>
                                </div>
                                <!--管理员发言start-->
                                <div class="liveKztGuans" :key="'adminfy'+lkey" v-if="item.usertype == 9">
                                    <div class="liveKztYkener">
                                        <div class="liveKztYkName">
                                            <div>
                                                <span class="guanfang">官方</span>
                                                <span>{{ item.username }}</span>
                                            </div>
                                        </div>
                                        <div class="liveKztYkText">
                                            <div class="extsContSpoa">
                                                <span v-html="item.content"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="liveKztYkeimg">
                                        <el-popover placement="bottom" width="180" trigger="hover">
                                            <div>
                                                {{ item.username }}
                                            </div>
                                            <div slot="reference" class="liveKztYkinfo">
                                                <el-image style="width: 44px; height: 44px"
                                                          :src="item.avatar"></el-image>
                                            </div>
                                        </el-popover>
                                    </div>
                                </div>
                                <!--管理员发言end-->
                                <!--用户发言-->
                                <div class="liveKztYouke" :key="'userfy'+lkey" v-else>
                                    <div class="liveKztYkeimg">
                                        <el-popover placement="bottom" width="180" trigger="hover">
                                            <div>
                                                <el-row align="middle" justify="center">
                                                    <el-col :span="8">
                                                        <el-image style="width: 44px; height: 44px"
                                                                  :src="item.avatar"></el-image>
                                                    </el-col>
                                                    <el-col :span="16">
                                                        <div style="display:inline-block;height: 45px;line-height: 45px;">
                                                            <h4>{{ item.username }}</h4>
                                                        </div>
                                                    </el-col>
                                                </el-row>
                                                <div style="display: flex;align-items: center;justify-content: center;">
                                                    <el-button size="mini" @click="setjy(lkey)" type="danger" plain
                                                               round>禁言
                                                    </el-button>
                                                </div>
                                            </div>
                                            <div slot="reference" class="liveKztYkinfo">
                                                <el-image style="width: 44px; height: 44px"
                                                          :src="item.avatar"></el-image>
                                            </div>
                                        </el-popover>
                                    </div>
                                    <div class="liveKztYkener">
                                        <div class="liveKztYkName">
                                            <span>{{ item.username }}</span>
                                        </div>
                                        <div class="liveKztYkText">
                                            <div class="extsContSpoa">
                                                <span v-html="item.content"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--用户发言end-->
                            </template>
                            <!--职位消息-->
                            <template v-else-if="item.msgtype == 'job' && item.job">
                                <div class="liveKztposts" :key="'job'+lkey">
                                    <div class="liveKztpoData">
                                        <div class="liveKztpoTite">
                                            <h3>{{ item.job.com_name }}</h3>
                                            <div>
                                                <span>{{ item.job.name }}</span>
                                                <span class="money">{{ item.job.salary }}</span>
                                            </div>
                                            <span
                                                    class="spantips">{{ item.job.citystr }}·{{ item.job.job_exp }}经验·{{ item.job.job_edu }}学历</span>
                                        </div>
                                        <div class="liveKztpobutn">
                                            <el-button type="primary" size="mini" round>
                                                <el-link :href="item.job.url" target="_blank">查看</el-link>
                                            </el-button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </template>
                    </div>
                    <div class="liveKztFroms">
                        <div class="liveKztFroIcon">
                            <!-- <div class="liveTubiao">
                                <img src="../../images/Frame.png" alt="" @click="t">
                                <div id="commonly" class="showbq" v-if="showface"></div>
                            </div> -->
                            <div class="liveTubiao">
                                <el-popover placement="top-start" width="500" trigger="click"
                                            popper-class="chat-face-cont">
                                    <ul id="face_content" class="chat_face">
                                        <li v-for="(item,index) in faceArr" :key="index"
                                            @click="faceInsert('face' + index + ' ', true)" :title="index">
                                            <img :src="item">
                                        </li>
                                    </ul>
                                    <el-button slot="reference">
                                        <img src="../../images/Frame.png" alt="">
                                    </el-button>
                                </el-popover>
                            </div>
                            <div class="liveJinyan">
                                <el-switch v-model="vistor_nochat" inactive-text="游客禁言"
                                           @change="vistorNochatClick"></el-switch>
                                <el-switch v-model="nochat" inactive-text="全部禁言" @change="nochatCheck"></el-switch>
                            </div>
                        </div>
                        <div class="liveKztFroArea">
                            <el-input ref="send_content" type="textarea" :rows="2" placeholder="请输入内容"
                                      v-model="content" @keyup.enter.native="socketcheck(1)">
                            </el-input>
                        </div>
                        <div class="liveKztFroFaso">
                            <el-button round size="mini" @click="socketcheck(1)">发送</el-button>
                        </div>
                    </div>
                </el-tab-pane>
                <el-tab-pane label="被禁言" name="jinyan">
                    <div v-if="jylist.length > 0">
                        <div v-for="(item, jykey) in jylist" :key="jykey" class="jydiv">
                            <div class="jyimgname">
                                <div>
                                    <el-image style="width: 44px; height: 44px" :src="item.photo"></el-image>
                                </div>
                                <div style="padding-left: 10px;">
                                    <h6>{{ item.name }}</h6>
                                </div>
                            </div>
                            <div>
                                <el-button size="mini" @click="removejy(jykey)" type="primary">取消禁言
                                </el-button>
                            </div>
                        </div>
                    </div>
                    <el-empty v-else :image-size="100" description="暂无数据"></el-empty>
                </el-tab-pane>
            </el-tabs>
        </div>
    </div>
</template>
<script>
    var socket = window.parent.homeapp.socket, xjhsyncid = null, xjhid = null, mine = null, ping_timer = null;
    var pastSend = 0;
    var chatpage = 1;
    var chatload = false;
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
            xinfo: {
                type: Object,
                default: () => {
                }
            },
        },
        data: function () {
            return {
                xid: '',
                activeName: 'hudong',
                ismore: false,
                chatlist: [],
                jylist: [],
                info: {},
                nochat: false,
                vistor_nochat: false,
                content: '',
                showface: false,
                facecont: '',
                faceArr: [],

                value1: true,
                url: 'https://fuss10.elemecdn.com/e/5d/4a731a90594a4af544c0c25941171jpeg.jpeg'
            }
        },
        mounted() {
            this.initFace();
			this.receiveSocket();
			this.hiddenCommon();
        },
        watch: {
            id: {
                handler(val, oldVal) {
                    this.xid = val;
                    xjhid = val;
                    this.initChatList()
                },
                immediate: true,
                deep: true,
            },
            list: {
                handler(val, oldVal) {
                    this.jylist = val;
                },
                immediate: true,
                deep: true,
            },
            xinfo: {
                handler(val, oldVal) {
                    this.info = val;
                    if (val.vistor_nochat == '1') {
                        this.vistor_nochat = true
                    } else {
                        this.vistor_nochat = false
                    }
                    if (val.nochat == '1') {
                        this.nochat = true
                    } else {
                        this.nochat = false
                    }
                    if (val.syncid) {
                        xjhsyncid = val.syncid
                        this.socketcheck(3)
                    }
                },
                immediate: true,
                deep: true,
            },
        },
        methods: {
			receiveSocket:function(){
				window.parent.homeapp.receiveMessage(this.getMessage);
			},
			getMessage: function(e){
				var that = this;
				if(e.type == 'xjhMessage'){
					var msg = e.data;
					if(msg.xjhid == 'xjh_' + xjhsyncid && (msg.id != mine.id || (msg.id == mine.id && msg.plat != 'pc')) && msg.msgtype != 'zan') {
						// 渲染消息内容
						var	newlog = {
						    chatid: msg.chatid,
							msgtype: msg.msgtype,
							avatar: msg.avatar,
							username: msg.username,
							usertype: msg.usertype,
							content: rexContent(msg.content)
						};
						if(msg.timestamp - pastSend > 1800 * 1000) {
							newlog.time = timeFormat(msg.timestamp);
							pastSend = msg.timestamp;
						}
						if(msg.job){
							newlog.job = msg.job;
						}
						that.chatlist.push(newlog);
						setTimeout(function(){
							var scroll = document.getElementById('chat_content').scrollHeight;
							chatScrollTo(scroll);
						},100);
					}
				}
			},
			hiddenCommon:function(){
				var that = this;
				// 检查页面是否后台运行
				var yunhdn, yunvsb;
				if (typeof document.hidden !== "undefined") {
					yunhdn = "hidden";
					yunvsb = "visibilitychange";
				} else if (typeof document.msHidden !== "undefined") {
					yunhdn = "msHidden";
					yunvsb = "msvisibilitychange";
				} else if (typeof document.webkitHidden !== "undefined") {
					yunhdn = "webkitHidden";
					yunvsb = "webkitvisibilitychange";
				}
				document.addEventListener(yunvsb, function(){
					// 前台运行后，如socket断开，重新连接
					if(!document[yunhdn]){
						if (!socket || (socket && socket.readyState != 1)) {
							socket = window.parent.socketObj;
							if (xjhsyncid) {
							    that.chatUnlogin();
							}
						}
					}
				});
			},
			// 未登录用户加载聊天数据
			chatUnlogin:function(){
			    httpPost('m=neirong&c=xjhlive&a=getUnloginToken', {chattype: 'admin'}).then(function (response) {
			        if (response.data.error == 0) {
						var res = response.data.data;
			            if(res){
			                var bindData = res.data;
			                bindData.group = 'xjh_' + xjhsyncid;
			                var bind = {
			                    type: 'bind',
			                    data: bindData
			                }
			                socket.send(JSON.stringify(bind));
			            }
			        }
			    }).catch(function (error) {
			        console.log(error);
			    })
			},
            //在焦点处插入内容
            faceInsert: function (str, nofocus) {
                var obj = this.$refs.send_content
                var r = focusInsert(obj, str, nofocus)
                if (r) {
                    this.content = r;
                }
            },
            initFace: function () {
                this.faceArr = faces()
            },
            initChatList: function(){
                this.chatlist = []
                chatload = false
                chatpage = 1
                this.getChat()
            },
            loadingMore: function () {
                if (chatload) {
                    chatload = false;
                    chatpage++;
                    this.getChat();
                }
            },
            getChat: function () {
                var that = this;
                httpPost('m=neirong&c=xjhlive&a=getXjhChatPage', {
                    xid: that.xid,
                    page: chatpage
                }).then(function (response) {
                    chatload = true
                    if (response.data.error == 0) {
                        that.ismore = response.data.data.ismore;
                        if (!mine) {
                            mine = {
                                id: 'gly',
                                avatar: response.data.data.chat_logo,
                                username: response.data.data.web_name,
                                usertype: 9
                            }
                        }
                        var scroll = null;
                        var logs = response.data.data.list;
                        for (var i = 0; i < logs.length; i++) {
                            logs[i].content = rexContent(logs[i].content);
                            var time = parseInt(logs[i].sendTime);
                            if (pastSend == 0 || (pastSend - time > 1800 * 1000)) {
                                logs[i].time = timeFormat(time);
                                pastSend = time;
                            }
                        }
                        logs.reverse();
                        that.chatlist = logs.concat(that.chatlist);
                        setTimeout(function () {
                            if (chatpage == 1) {
                                // 第一次加载需要自动滚动
                                scroll = document.getElementById('chat_content').scrollHeight;
                                chatScrollTo(scroll);
                            }
                        }, 100);
                    } else {
                        message.error(response.data.msg);
                    }
                }).catch(function (error) {
                    console.log(error);
                })
            },
            setjy: function (k) {
                var log = this.chatlist[k];
                var that = this;
                httpPost('m=neirong&c=xjhlive&a=setjy', {chatid: log.chatid}).then(function (response) {
                    if (response.data.error == 0) {
                        message.success(response.data.msg, function () {
                            var jy = {
                                uid: log.id,
                                usertype: log.usertype,
                                name: log.username,
                                photo: log.avatar,
                                xid: log.xid
                            };
                            that.jylist.unshift(jy);
                        });
                    } else {
                        message.error(response.data.msg);
                    }
                }).catch(function (error) {
                    console.log(error);
                })
            },
            removejy: function (k) {
                var jy = this.jylist[k];
                var that = this;
                httpPost('m=neirong&c=xjhlive&a=removejy', {
                    uid: jy.uid,
                    usertype: jy.usertype,
                    xid: jy.xid
                }).then(function (response) {
                    if (response.data.error == 0) {
                        that.jylist.splice(k, 1);
                    } else {
                        message.error(response.data.msg);
                    }
                }).catch(function (error) {
                    console.log(error);
                })
            },
            // 游客禁言
            vistorNochatClick: function (val) {
                this.vistor_nochat = val
                httpPost('m=neirong&c=xjhlive&a=setNochat', {
                    xid: this.xid,
                    vistor_nochat: this.vistor_nochat ? '1' : '0'
                }).then(function (response) {

                }).catch(function (error) {
                    console.log(error);
                })
            },
            socketcheck(type, job = null) {
                var that = this
                if (socket && socket.readyState == 1) {
                    if (type == 1) {
                        that.send();
                    } else if (type == 2) {
                        that.nochatClick();
                    } else if (type == 3) {
                        that.chatUnlogin();
                    } else if (type == 4) {
                        that.sendMessage('jobid=' + job.id, 'job', job)
                    }
                } else {
                    window.parent.homeapp.websocket()
                    var timer = setInterval(function () {
                        if (window.parent.homeapp.socket && window.parent.homeapp.socket.readyState == 1) {
                            socket = window.parent.homeapp.socket
                            clearInterval(timer);
                            if (type == 1) {
                                that.send();
                            } else if (type == 2) {
                                that.nochatClick();
                            } else if (type == 3) {
                                that.chatUnlogin();
                            } else if (type == 4) {
                                that.sendMessage('jobid=' + job.id, 'job', job)
                            }
                        }
                    }, 100);
                    setTimeout(function () {
                        if (timer) {
                            clearInterval(timer);
                        }
                    }, 10000);
                }
            },
            // 全部禁言校验
            nochatCheck: function (val) {
                this.nochat = val;
                this.socketcheck(2)
            },
            // 全部禁言
            nochatClick: function () {
                var that = this
                var msg = {
                    type: 'xjhChatting',
                    data: {
                        to: {xjhid: that.xid, chatting: that.nochat ? '1' : '0'}
                    }
                };
                httpPost('m=neirong&c=xjhlive&a=setNochat', {
                    xid: that.xid,
                    nochat: that.nochat ? '1' : '0'
                }).then(function (response) {
                    if (socket && socket.readyState == 1) {
                        socket.send(JSON.stringify(msg));
                    } else {
                        message.error('通知禁言功能加载中');
                    }
                }).catch(function (error) {
                    console.log(error);
                })
            },
            // 发送消息
            send: function () {
                if (!this.content) {
                    message.error('请输入要发送的内容');
                    return false
                }
                this.sendMessage(this.content, 'char');
                this.content = '';
            },
            // 发送消息
            sendMessage: function (content, type, obj = null) {
                var that = this
                var time = new Date().getTime();
                if (socket && socket.readyState == 1) {
                    var mineData = {
                        id: mine.id,
                        avatar: mine.avatar,
                        username: mine.username,
                        usertype: mine.usertype,
                        content: content,
                        timestamp: new Date().getTime(),
                        xjhid: 'xjh_' + xjhsyncid,
                        xid: xjhid,
                        msgtype: type,
                        plat: 'pc'
                    };
                    if (type == 'job' && obj) {
                        mineData.job = obj;
                    }
                    var msg = {
                        type: 'xjhMessage',
                        data: {
                            mine: mineData
                        }
                    };
                    var pdata = {
                        content: content,
                        timestamp: mineData.timestamp,
                        msgtype: mineData.msgtype,
                        pytoken: pytoken,
                        xjhid: xjhid
                    };
                    httpPost('m=neirong&c=xjhlive&a=chatlog', pdata).then(function (response) {
                        if (response.data.error == 0) {
                            socket.send(JSON.stringify(msg));
                            // 渲染消息内容
                            mineData.content = rexContent(content);
                            if (mineData.timestamp - pastSend > 1800 * 1000) {
                                mineData.time = timeFormat(mineData.timestamp);
                                pastSend = mineData.timestamp;
                            }
                            that.chatlist.push(mineData);
							// 自动滚动
							setTimeout(function(){
								var scroll = document.getElementById('chat_content').scrollHeight;
								chatScrollTo(scroll);
							},100);
                        }
                    }).catch(function (error) {
                        console.log(error);
                    })
                } else {
                    message.error('功能加载中', function () {
                        webSocket(window.parent.homeapp.socketUrl)
                    });
                }
            },
            // handleClick(tab, event) {
            //     if (tab == 'hudong') {
            //         chatpage = 1;
            //         this.getChat()
            //     } else {
            //
            //     }
            //     console.log(tab, event);
            // }
        },
    };
</script>
<style>
    .chat-face-cont .chat_face {
        overflow-y: auto;
        height: 200px;
        margin: 10px 0;
        position: relative;
    }

    .chat-face-cont .chat_face li {
        overflow: hidden;
        float: left;
    }

    .chat-face-cont .chat_face li img {
        width: 40px;
        height: 40px;
        margin: 3px 5px;
    }
</style>
<style scoped>
    .loadingMore {
        overflow: hidden;
        /*height: 0.4rem;*/
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .loadingMore span {
        /*font-size: 0.14rem;*/
        color: #AEB0C4;
        cursor: pointer;
    }

    .jydiv {
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: space-between;
        height: 65px;
        border-bottom: 1px solid #374366;
    }

    .jyimgname {
        display: flex;
        align-items: center;
        justify-content: space-between;
        color: #fff
    }

    .chat_bq_img {
        width: 40px;
        height: 40px;
        /*margin: 3px 5px;*/
    }
</style>