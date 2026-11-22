<template>
    <div class="moduleElHight" style="padding: 0 0px;">
        <div class="crmChatDomes">
			<div id="chatList" class="crmChatZuobaSub">
				<el-tabs tab-position="left" v-model="toid" @tab-click="tabClick">
					<el-tab-pane  v-for="(item,pk) in chatList" :key="pk" :label="item.linkman" :name="item.id.toString()">
						<div slot="label" class="crmChatLabel">
							<div class="crmChatLabImg">
								<el-image :src="item.avatar"></el-image>
								<em class="kefu_chat_number" v-if="item.unread>0">{{item.unread}}</em>
							</div>
							<div class="crmChatLabTite">
								<div class="crmChatLabName">
									<span>{{item.linkman}}</span>
									<span class="spanminc"  v-if='item.expect!=""'>{{item.expect}}</span>
								</div>
								<div class="crmChatLabIfo">
									<span>{{item.content}}</span>
									<span>{{item.time}}</span>
								</div>
							</div>
						</div>
					</el-tab-pane>
				</el-tabs>
			</div>
			<div class="crmChatNeirs" v-if="toid">
				<el-tabs type="border-card" v-model="activeName">
					<el-tab-pane  label="聊天" name="1">
						<div class="chatSubjects">
							<div class="chatSubJIlv chatSubKeQins">
								<ul id="chat_content">
									<li  v-for="(item, ck1) in logList" :key="ck1">
										<div class="kefu_chat_time" v-if="item.timeval">{{item.timeval}}</div>
										
										<!--左-->
										<div class="kefu_chat_pleft" v-if="!item.mine">
											<div class="kefu_chat_ptx"><el-image :src="item.avatar" width="50" height="50"></el-image></div>
											<div class="kefu_chat_p" v-if="item.image">
												<span>
													<el-image  :src="item.image" ></el-image>
												</span>
											</div>
											<div class="kefu_chat_p" v-else>
												<span v-for="(cn, cnk) in item.content" :key="cnk">
													<!--表情-->
													<img v-if="cn.t == 'emoji'" class="chat_bq_img" :src="cn.n">
													<!--文字-->
													<span v-else-if="cn.t == 'char'">
														<em>{{cn.n}}</em>
													</span>
												</span>
											</div>
										</div>
										<!--右-->
										<div class="kefu_chat_prightbox" v-else>
											<div class="kefu_chat_pright">
												<!-- <el-image :src="item.avatar" width="50" height="50"></el-image> -->
												<template v-if="item.job">
													<div class="chat_yx_job" @click="toShow(item.job.webjob_url)">
														<div class="chat_yx_job_name">
															<span>{{item.job.name}}</span>
															<span class="chat_yx_xz">{{item.job.job_salary}}</span>
														</div>

														<div class="chat_yx_com_name">{{item.job.com_name}}</div>
														<div class="chat_yx_com_tipsa">
															{{item.job.citystr}}.{{item.job.job_exp || ''}}经验.{{item.job.job_edu || ''}}学历
														</div>
													</div>
												</template>
												<template v-else-if="item.image">
													<div class="kefu_chat_text">
														<div class="kefu_chat_p">
															<span>
																<el-image  :src="item.image" ></el-image>
															</span>
														</div>
														<div class="pc_kefu_chat_read">{{item.read ? '已读' : '未读'}}</div>
													</div>
												</template>
												<template v-else>
													<div class="kefu_chat_text">
														<div class="kefu_chat_p">
															<span v-for="(cn, cnk) in item.content" :key="cnk">
																<!--表情-->
																<img v-if="cn.t == 'emoji'" class="chat_bq_img" :src="cn.n">
																<!--文字-->
																<span v-else-if="cn.t == 'char'">
																	<em>{{cn.n}}</em>
																</span>
															</span>
														</div>
														<div class="pc_kefu_chat_read">{{item.read ? '已读' : '未读'}}</div>
													</div>
												</template>
												<div class="kefu_chat_ptx"><el-image :src="item.avatar" width="50" height="50"></el-image></div>
											</div>
										
										</div>
									</li>
								</ul>
							</div>
							<div class="chatSubFrom">
								<div class="chatSubFromicon">
									<div class="fromiconone">
										<el-popover placement="right" width="400" trigger="manual" v-model="usefulpop">
											<div class="cyy_box">
												<div class="cyy_box_tit">
													<span class="cyy_box_titname">常用语</span>
												</div>
												<div class="cyy_show" v-if="usefullist.length>0">
													<div v-for="(item,uk) in usefullist" :key="uk" class="cyy_box_list" @click="sendUseful(item)">
														<span class="cyy_box_name">{{item.content}}</span>  
														<el-link :underline="false" type="primary">发送</el-link>
													</div>
												</div>
												<el-empty description="目前没有添加常用语" v-else></el-empty>
											</div>
											
											<img src="../../images/chat_cyy.png" alt="" slot="reference"  @click="usefulpop = !usefulpop">
										</el-popover>
									</div>
									<div class="fromiconone">
										<el-popover placement="top" width="400" trigger="manual" v-model="emojipop">
											<ul class="chat_face imageIcons">
												<li @click="faceTap(fk)" v-for="(item,fk) in facelist" :key="fk" :title="fk">
													<img :src="item">
												</li>
											</ul>
											<img src="../../images/chat_bq.png" alt="" slot="reference"  @click="emojipop = !emojipop">
										</el-popover>
									</div>
									<div class="fromiconone">
										<el-upload
											:key="timer"
											class="upload-demo"
											:accept="pic_accept"
											action="#"
											:auto-upload="false"
											:show-file-list="false"
											:multiple='false'
											:limit="1"
											:on-change="uppic">
											<img src="../../images/chat_tj.png" alt="">
										</el-upload>
									</div>
									<div class="fromicontwo"  @click="showJobList()"><span>发职位</span></div>
								</div>
								<div class="chatSubFromTextar">
									<el-input type="textarea" v-model="content" @keyup.enter.native="sendcontent"></el-input>
								</div>
								<div class="chatSubFromFason">
									<div class="chatSubFromfsannie">
										<span> 按Enter发送 </span>
										<button @click="sendcontent">发送</button>
									</div>
								</div>
							</div>
						</div>
					</el-tab-pane>
					<el-tab-pane label="简历详情" name="2">
						<div style="overflow: hidden; position: relative; padding: 12px; width: calc(100% - 24px);height: calc(100% - 24px);">
							<resumeedit :uid="toid"></resumeedit>
						</div>
						
					</el-tab-pane>
				</el-tabs>
			</div>
        </div>
		<div class="modluDrawer">
			<el-drawer title="发送职位" :visible.sync="job_show" :modal-append-to-body="false" append-to-body :show-close="true"
				:with-header="true" size="80%">
				<chatjoblist v-if="job_show" :eid="eid" @send-job="sendjob"></chatjoblist>
			</el-drawer>
		</div>
    </div>
</template>
    
<script>
var socket = window.parent.socketObj;
var mine = null;
var listLoad = false;
module.exports = {
    props: {
        uid: {
            type: [Number, String],
            default: ''
        },
    },
    data: function () {
        return {
            pic_accept: localStorage.getItem("pic_accept"),
			timer:'0',
			toid:'',
			eid:'',
			totype:1,
			listpage:1,
			logList: [],
			chatList: [],
			expect: {},
			joblist: [],
			total: 0,
			ismore:false,
			
			job_show:false,
			activeName:'1',
			send_job:[],
			
			emojipop:false,
			facelist:[],
			
			usefulpop:false,
			usefullist:[],
			
			content:'',
			chated:0,
			
			listpage:1,
			loaded:true,
			lastid:'',
			page:1,
		}
    },
	components: {
		'chatjoblist': httpVueLoader('./chatjoblist.vue'),
		'resumeedit': httpVueLoader('./resumeedit.vue'),
	},
    mounted() {
		var that = this;
		this.toid = this.uid;
		this.facelist = faces();
		this.getData();
		this.getDetail();
		this.hiddenCommon();
		this.receiveSocket();
		
		that.$nextTick(() => {
			
			boxElement = document.getElementById("chatList");
			boxElement.addEventListener('scroll', function(){
				if (boxElement.scrollHeight - boxElement.scrollTop === boxElement.clientHeight && listLoad) {
					that.getList();
				}
			});
		
		
			$("#chat_content").scroll(function(){
				var cs = document.getElementById('chat_content').scrollTop;
				
				if(cs == 0 && that.ismore && that.loaded){
					document.getElementById('chat_content').scrollTop = 21;
					that.loaded = false;
					that.getChatPage();
				}
			})
			
		});
	},
    methods: {
		receiveSocket:function(){
			window.parent.homeapp.receiveMessage(this.getMessage);
		},
		getMessage:function(msg){
			var that = this;
			var toid = this.toid;
			if (msg.id && msg.totype == 9){
				if (toid != '') {
					
					if(msg.msgtype != 'change' && msg.msgtype != 'read' ){
						var chatList = deepClone(that.chatList);
						for(let i in chatList){
							if(chatList[i].id == msg.id){
								chatList[i].timestamp = msg.timestamp;
								chatList[i].time = timeFormat(msg.timestamp);
								if(msg.id !=toid){
									if(parseInt(chatList[i].unread)>0){
										var num = parseInt(chatList[i].unread) + 1;
										chatList[i].unread = num;
									}else{
										chatList[i].unread = 1;
									}
								}
								
							}
						}
		
						this.chatList = sortByField(chatList,'timestamp','desc');
					}
					
					var chatList = deepClone(that.chatList);
					this.chatRender(msg);
					var desc = setChatDesc(msg);
					for(let i in chatList){
						if(chatList[i].id==msg.id){
							chatList[i].content = desc;
						}
					}
					this.chatList = chatList;
				}
			}
		},
		chatRender:function (msg){
			var that = this;
			var toid = this.toid;
			// 渲染消息内容
			if(msg.msgtype != 'read' && msg.msgtype != 'recall' ){
				var	newlog = {
				    chatid: msg.chatid,
					mine: false,
					msgtype: msg.msgtype,
					avatar: msg.avatar,
					content: msg.content
				};
				if(msg.content.indexOf('img[') != -1){
					newlog.image = msg.content.replace(/(^img\[)|(\]$)/g, '');
				}else{
					newlog.content = rexContent(msg.content);
				}
				this.logList.push(newlog);
				that.$nextTick(() => {
				    var scroll = document.getElementById('chat_content').scrollHeight;
					
				    chatScrollTo(scroll);
				});
				
				setTimeout(function() {
					httpPost('m=crm&c=crm_chat&a=setMsgStatus',{
						id: toid,
						type: that.totype,
						nowid: mine.id,
						nowtype: mine.usertype,
					}).then((result) => {
					    that.sendMessage('', 'read', msg.chatid);
					}).catch(function (e) {
					    console.log(e)
					})
				}, 500);
			}else{
				var list = this.logList;
				for(var i in list){
					if(list[i].read == 0){
						list[i].read = 1;
					}
				}
				this.logList = [];
				this.logList = list;
			}
		},
		resetdata:function(){
			this.content = '';
			this.chated = 0;
			this.eid = '';
			this.listpage = 1;
			this.loaded = true;
			this.lastid = '';
			this.page = 1;
			this.send_job = [];
			this.ismore = false;
			this.expect = {};
			this.logList = [];
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
						that.getData();
					}
				}
			});
		},
		async getData() {
		    let self = this;
		    var toid = this.toid;
		    startLoading();
			
		    httpPost('m=crm&c=crm_chat&a=room',{}).then((result) => {
		        endLoading();
				
		        var data = result.data;
				var res = data.data;
				if (data.error == 0) {
		            self.usefullist = res.usefullist;
					
					var t = setInterval(function(){
						if (socket && socket.readyState == 1){
							clearInterval(t);
							if(res.data){
								var bindData = res.data;
								var bind = {
									type: 'bind',
									data: bindData
								}
								socket.send(JSON.stringify(bind));
							}
							if(res.mine){
								mine = res.mine;
							}
							window.localStorage.setItem("islink", "1");
							if (toid != '') {
								setTimeout(function() {
									self.sendMessage('', 'read', 'all');
								}, 800);
							}
						}
					},100);
					setTimeout(function(){
						if(t){
							clearInterval(t);
						}
					},10000);
								
				}
		    }).catch(function (e) {
		        console.log(e)
		    })
		},
		getList: function() {
			var self = this;
			listLoad = false;
			
			httpPost('m=crm&c=crm_chat&a=chatList',{page:self.listpage}).then((result) => {
			    endLoading();
				var res = result.data;
				if(res.error==0){
					listLoad = true;
					self.listpage++;
					if (res.data.list.length > 0) {
						self.chatList = self.chatList == 0 ? res.data.list : self.chatList.concat(res.data.list);
					}
				}
			    
			}).catch(function (e) {
			    console.log(e)
			})
		},
		getChatPage: function(id) {
			var self = this;
			
			httpPost('m=crm&c=crm_chat&a=getChatPage',{id:self.toid,page:self.page,lastid:self.lastid}).then((result) => {
			    
				var res = result.data.data;
				self.page++;
				// 第二页开始，记录前一次滚动距离
				var scroll = getScrollHeight();

				if (res.code == 0) {
					var chatlog = res.data;
					if (chatlog.length > 0) {
						
						var list = [];
						
						var adminjob = res.adminjob;
						
						for (let i = 0; i < chatlog.length; i++) {
							if (chatlog[i].msgtype == 'adminjob' && chatlog[i].content.indexOf('jobid') != -1) {
								var jid = chatlog[i].content.replace('jobid=', '');
								if (adminjob[jid]) {
									chatlog[i].job = adminjob[jid];
								}
							}else if(chatlog[i].content.indexOf('img[') != -1){
								chatlog[i].image = chatlog[i].content.replace(/(^img\[)|(\]$)/g, '');
							}else{
								chatlog[i].content = rexContent(chatlog[i].content);
							}
							
							list.push(chatlog[i]);
							// 最后一条时，如还有历史消息，增加更多消息
							if (i == chatlog.length - 1 && res.ismore) {
								self.ismore = true;
								self.lastid = chatlog[i]['id'];
							} else {
								self.ismore = false;
							}
						}
						
						list.reverse();
						self.logList = list.concat(self.logList);
					}
				}
				if (scroll) {
					self.$nextTick(function() {
						// 渲染后，滚动到上一页的距离
						var newScroll = getScrollHeight();
						chatScrollTo(newScroll - scroll);
					})
				}
			    
			}).catch(function (e) {
			    console.log(e)
			})
			
		},
		tabClick:function(){
			this.chatList = [];
			this.resetdata();
			this.getDetail();
		},
		async getDetail() {
		    let self = this;
		    let params = {
				toid:this.toid
			}
			listLoad = false;
		    startLoading();
		    httpPost('m=crm&c=crm_chat&a=detail', params).then((result) => {
		        endLoading();
				listLoad = true;
				self.listpage++;
		        var data = result.data;
				var res = data.data;
				if (data.error == 0) {
		            if (res.list.length > 0) {
						self.chatList = self.chatList.length == 0 ? res.list : self.chatList.concat(res.list);
					}
					if (res.data) {
						self.expect = res.data.expect;
						self.eid = res.data.expect.id;
					}
					if (res.log && res.log.data.length > 0) {
						var adminjob = res.log.adminjob;
						var logs = res.log.data;
						var scroll = null;
						for (let i = 0; i < logs.length; i++) {
							if (logs[i].msgtype == 'adminjob' && logs[i].content.indexOf('jobid') != -1) {
								var jid = logs[i].content.replace('jobid=', '');
								if (adminjob[jid]) {
									logs[i].job = adminjob[jid];
								}
							}else{
								if(logs[i].content.indexOf('img[') != -1){
									logs[i].image = logs[i].content.replace(/(^img\[)|(\]$)/g, '');
								}else{
									logs[i].content = rexContent(logs[i].content);
								}
							}
						}
						self.logList = logs.reverse();
						
						self.$nextTick(() => {
							var scroll = document.getElementById('chat_content').scrollHeight;
							chatScrollTo(scroll);
						});
						
						
						self.ismore = res.log.ismore;
						self.loaded = true;
					}
		        }
		    }).catch(function (e) {
		        console.log(e)
		    })
		},
		showJobList:function(){
			this.job_show = true;
		},
		sendjob:function(row){
			this.send_job = row.job;
			this.sendMessage('', 'adminjob',row.job.id);
			this.job_show = false;
		},
		faceTap: function(face) {
			this.content += ' face' + face;
			this.emojipop = false;
		},
		sendUseful:function(useful){
			var content = useful.content;
			this.sendMessage(content,'char','');
			this.usefulpop = false;
		},
		uppic:function(file,fileList){
			let self = this;
			
			var params = new FormData();
			params.append('file',file.raw);
			
			startLoading();
			
			self.timer = new Date().getTime();
			
			httpPost('m=crm&c=crm_chat&a=uploadImage',params).then((result) => {
			    endLoading();
				var res = result.data;
				if(res.error==0){
					self.sendMessage('img[' + (res.data.url || '') + ']','char','');
				}else{
					message.error(res.msg);
				}
			    
			}).catch(function (e) {
			    console.log(e)
			})
		},
		sendcontent:function(){
			var that = this;
			if(socket && socket.readyState == 1){
				that.send();
			}else{
				socket = window.parent.socketObj;
				var timer = setInterval(function(){
					if(socket && socket.readyState == 1){
						clearInterval(timer);
						that.send();
					}
				},100);
				setTimeout(function(){
					if(timer){
						clearInterval(timer);
					}
				},10000);
			}
		},
		commonHide:function(){
			this.job_show = false;
			this.emojipop = false;
			this.usefulpop = false;
		},
		send:function(){
			if(this.content!=''){
				this.sendMessage(this.content,'char','');
				this.content = '';
			}
		},
		sendMessage:function(content, type, id){
			var that = this;
			var toid = that.toid;
			that.commonHide();
			var time = new Date().getTime();
			var chatList = deepClone(that.chatList);
			if(socket && socket.readyState == 1){
				// 根据消息类型区分保存内容
				if (type == 'adminjob') {
					content = 'jobid=' + id;
				}else if (type == 'read') {
					content = 'read=' + id;
				}
				var mineData = {
					id: mine.id,
					mine: true,
					ftype: mine.usertype,
					avatar: mine.avatar,
					username: mine.username,
					linkman: mine.linkman,
					timestamp: time,
					content: content,
					msgtype: type,
					op: {},
				};
				var sendData = {
					mine: mineData,
					to: {
						id: toid,
						utype: that.totype
					}
				};
				var message = {
					type: 'chatMessage',
					data: {
						content: sendData
					}
				};
				// 保存聊天记录
				if (type != 'change' && type != 'read' ) {
					var pdata = {
						to: sendData.to.id,
						totype: sendData.to.utype,
						content: content,
						timestamp: sendData.mine.timestamp,
						msgtype: sendData.mine.msgtype,
						nowid: sendData.mine.id,
						nowtype: sendData.mine.ftype,
						eid: that.eid,
						chated: that.chated
					};
					httpPost('m=crm&c=crm_chat&a=chatLog',pdata).then((result) => {
					    var data = result.data;
					    var res = data.data;
						if (res.error == 0) {
							that.chated = 1;
							message.data.content.mine.chatid = res.chatid;
							socket.send(JSON.stringify(message));
							// 渲染消息内容
							if(content.indexOf('img[') != -1){
								mineData.image = content.replace(/(^img\[)|(\]$)/g, '');
							}else{
								mineData.content = rexContent(content);
							}
							mineData.read = 0;
							if (type == 'adminjob') {
								mineData.job = this.send_job;
							}
							for(let i in chatList){
								if(chatList[i].id == toid){
									chatList[i].timestamp = time;
								}
							}
							var desc = setChatDesc(mineData);
							for(let i in chatList){
								if(chatList[i].id==mineData.id){
									chatList[i].content = desc;
								}
							}	
							
							that.chatList = sortByField(chatList,'timestamp','desc');
							
							that.logList.push(mineData);
							
							that.$nextTick(() => {
							    var scroll = document.getElementById('chat_content').scrollHeight;
							    chatScrollTo(scroll);
							});
							
							// if(typeof parent.needLoad !== 'undefined'){
							// 	parent.needLoad = true;
							// }
						}
					}).catch(function (e) {
					    console.log(e)
					})
					
				} else {
					socket.send(JSON.stringify(message));
					// 渲染消息内容
					if (type != 'read' ) {
						mineData.content = rexContent(content);
						mineData.read = 0;
						self.logList.push(mineData);
						self.$nextTick(() => {
						    var scroll = document.getElementById('chat_content').scrollHeight;
						    chatScrollTo(scroll);
						});
					}
				}
			}else{
				message.error('客勤功能加载中');
			}
		}
    },
};
</script>
<style scoped>
.moduleElTaCaoz {
    justify-content: space-between;
}

.moduleElTaCaoz .el-button {
    width: 57px;
    margin: 3px 0;
}
.crmChatDomes{
	display: flex;
	align-items: center;
}
.crmChatDomes #chatList{
	overflow: hidden;
	position: relative;
	width: 300px;
	height: 100%;
}
.crmChatDomes .crmChatNeirs{
	overflow: hidden;
	position: relative;
	width: calc(100% - 300px);
	height: 100%;
}
.crmChatNeirs .el-tab-pane{
	overflow: hidden;
	position: relative;
	width: 100%;
	height: 100%;
}

</style>