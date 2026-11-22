var socket = null,
	islink = 1,
	chatright = 0,
	ping_timer = null,
	lastid = '',
	sendTime = 0,
	chatpage = 1,
	
	newChatMsg = false,
	
	
	titledsq = null,
	isIELowVision = false,
	
	chatlistch = 0,
	boxElement = null,
	mhpage = 0,
	mhdata = true,
	commentState = null,
	spconfirm = null,
	spint = null,
	zphnetSocketOnline = [],
	spviewBell = null,
	canSend = false,
	moreReady = false,
	pastSend = 0,
	unreadchat = [],
	comalljobnum = 0;

var chatapp = new Vue({
	el: '#chatapp',
	data: {
		get_id:typeof get_id!='undefined'?get_id:'',
		get_type:typeof get_type!='undefined'?get_type:'',

		toid:'',
		totype:'',
		uid:typeof uid!='undefined'?uid:'',
		usertype:typeof usertype!='undefined'?usertype:'',

		showUnread:false,

		adminChat:'',
		cantel:'',
		canwx:'',
		

		receive:{},
		
		greeting_switch:'',
		greeting_id:'',
		greeting:'',

		is_zdrat:0,

		zd_switch:'',

		usefullist:[],

		zdconlist:[],

		chatlist:[],

		config:{},
		company:{},
		chatStorage:[],

		lastjob_arr:[],//企业与所有聊天对象最新的职位数组
		lastjob:null,
		lastjobshow:false,

		expectStorage: [], // 简历详细本地缓存
		expectInfo: {}, // 展示的数据
		expectOpen: true, // 右侧打开

		inviteNum:0,
		linkman:'',
		stime: '',
		identity:'',
		towx:'',
		totel:'',
		jobShowed:true,
		playMsgid:null,
		expect:[],
		joblist:[],
		adminjob:null,
		chatjob:null,

		chatlog:[],
		headshow:false,

		morelog:false,
		chatlist_load:true,

		newchatid:'',
	
	},
	created: function() {
		$("#chatapp").css('display', 'block');
		if(typeof isAdmin == 'undefined'){
			this.getData();
		}
	},
	methods:{
		getData:function(){
			var self = this;
			
			$.post(weburl+"/index.php?m=chat&c=pageData", {
				id: self.get_id,
				type: self.get_type
			}, function(res) {
				
				if (res.errcode == 1) {

					self.adminChat = res.adminChat;

					if(res.receive){
						self.receive = res.receive;
					}
					self.is_zdrat = res.is_zdrat;

					self.zd_switch = res.zd_switch;

					self.greeting_switch = res.greeting_switch;

					self.greeting = res.greeting;

					self.greeting_id = res.greeting_id;

					self.config = res.config;


				}else if(res.errcode == 2){
					layer.msg('请先登录', 2, 8,function(){
						window.location.href="index.php?m=login";
					});
					return false;
				}

			}, 'json');
		},
		readlist:function(){

			var that = this;

			var showUnread = that.showUnread;

			that.showUnread = !that.showUnread;

			if(!showUnread){
				
				that.chatlist_load = true;
				$.post(weburl+"/index.php?m=chat&c=showUnread", {
					rand:Math.random()
				}, function(res) {
					
					that.chatlist_load = false;

					if (res.error == 0) {
						mhdata = false;

						var cstorage = res.chatstorage;
						for(var i in cstorage){
							chatapp.$data.chatStorage[i] = cstorage[i];
						}

						var his = res.history;

						chatapp.$data.chatlist = his;
						
					}

				}, 'json');
			}else{
				mhdata = true;
				getAllChatOnlineData(toid)
			}
			
		},
		inviteResumeClick: function(){
			if(this.inviteNum>0){
				layer.msg('您已邀请了该用户面试', 2, 9);
			}else{
				$("#uid").val(toid);
				inviteResume("#inviteCheck");
			}
		},
		/* 会员中心判断聊天权利*/
		toChat:function(id, type, nowtype) {
			var thischatStorage = getChatLocalStorage(id);
			if(thischatStorage && typeof thischatStorage.toid!='undefined' && thischatStorage.toid){
				initData(thischatStorage);
			}else{
				// 将聊天记录置空
				chatapp.$data.chatlog = [];
				showSingle(id, type);
			}

			// 企业切换聊天同步切换简历
			if (nowtype == 2 && type == 1) { // 企业 个人
				// console.log('企业切换聊天同步切换简历', id, type, nowtype)
				if(chatapp.$data.lastjob_arr[id] && typeof chatapp.$data.lastjob_arr[id]!='undefined'){
					var jobid = chatapp.$data.lastjob_arr[id];

					if(typeof chatapp.$data.joblist[jobid]!='undefined'&&chatapp.$data.joblist[jobid]){
						chatapp.$data.lastjob = chatapp.$data.joblist[jobid];
					}
					
				}
				this.showExpect(id);
				this.changeChatlist(1);
			}
		},
		confirmAsk: function(ask,chatid) {
			$('#sendtype').val('sendconfirm');
			$('#hhtype').val(ask);
			$('#chatlogid').val(chatid);
			if (ask == 'tel') {

				$('#wxdiv').hide();
				$('#teldiv').show();
				if (tel) {
					$('#telinput').val(tel);
				}
				$('#hhshow').show();

			} else if (ask == 'wx') {

				$('#teldiv').hide();
				$('#wxdiv').show();
				if (wxid) {
					$('#wxinput').val(wxid);
				}
				$('#hhshow').show();
			}
		},
		// 发送常用语消息
		sendUseful:function(content){
			if(typeof content != 'undefined'){
				sendMessage(content,'char','');
				closeUseful();
			}else{
				layer.msg('数据异常', 2, 8);
			}
		},
		editUseful:function(key){
			var usefulid = '',
				usefulcon= '',
				usefullist = deepClone(this.usefullist);

			if(typeof key != 'undefined' && typeof usefullist[key].id != 'undefined' && usefullist[key].id!=''){
				usefulid	= usefullist[key].id;
				usefulcon	= usefullist[key].content;
			}
			
			$('#usefulid').val(usefulid);
			$('#usefulcon').val(usefulcon);
			$('#usefultype').val('');
			$('#usefulAddBox').show();
		},
		//删除常用语
		delUseful:function(key){

			var usefullist = deepClone(this.usefullist);

			if(typeof key != 'undefined' && typeof usefullist[key].id !='undefined' && usefullist[key].id !=''){

				var id = usefullist[key].id;

				var loadi = loadlayer();
				$.post(weburl + "/index.php?m=chat&c=delUseful", {
					id: id
				}, function(res) {
					layer.closeAll();
					if(res.errcode == 9){
						usefullist.splice(key, 1);
						this.usefullist = usefullist;
						usefulDefConfig();
						layer.msg(res.msg, 2, 9);
					}else{
						layer.msg(res.msg, 2, 8);
					}
				}, 'json');
			}else{
				layer.msg('数据异常', 2, 8);
			}
		},
		editZdcon:function(key){
			var zdconid = '',
				zdconcon= '',
				zdconkeyword= '',
				zdconsort= '',
				zdcontype= '',
				zdconlist = deepClone(this.zdconlist);

			if(typeof key != 'undefined' && typeof zdconlist[key].id != 'undefined' && zdconlist[key].id!=''){
				zdconid	= zdconlist[key].id;
				zdconcon	= zdconlist[key].content;
				zdconkeyword= zdconlist[key].keyword;
				zdcontype	= zdconlist[key].type;
				zdconsort	= zdconlist[key].sort;
			}
			
			$('#zdconid').val(zdconid);
			$('#zdconcon').val(zdconcon);
			$('#zdconkeyword').val(zdconkeyword);
			$('#zdconsort').val(zdconsort);
			var zdtype = zdcontype==1?true:false;
			$("#zdcontype").prop("checked",zdtype);
			zdconSetShow();
			
		},
		//删除常用语
		delZdcon:function(key){

			var zdconlist = deepClone(this.zdconlist);

			if(typeof key != 'undefined' && typeof zdconlist[key].id !='undefined' && zdconlist[key].id !=''){

				var zdconfirm = layer.confirm('确认删除？', {btn: ['确认', '算了']},
					function() {
						var id = zdconlist[key].id;

						var loadi = loadlayer();
						$.post(weburl + "/index.php?m=chat&c=delZdcon", {
							id: id
						}, function(res) {
							layer.closeAll();
							if(res.errcode == 9){
								zdconlist.splice(key, 1);
								this.zdconlist = zdconlist;
								zdconDefConfig();
								layer.msg(res.msg, 2, 9);
							}else{
								layer.msg(res.msg, 2, 8);
							}
						}, 'json');
					},
					function() {
						layer.close(zdconfirm);
					}
				);
				
			}else{
				layer.msg('数据异常', 2, 8);
			}
		},
		// 消息撤回
		recall:function(chatid, time){
			
			if(recalltime > 0){
				
				var now = new Date().getTime();
				
				if(now - parseInt(time) < (recalltime * 60 * 1000)){
					
					layer.open({
						content: '确定要撤回消息吗？',
						btn: ['确定', '取消'],
						yes: function(index) {
							layer.close(index);
							sendMessage('', 'recall', chatid);
						}
					});
				}
			}
		},
		sendAsk:function(ask) {
			$('#sendtype').val('sendask');
			$('#hhtype').val(ask);

			if (ask == 'tel') {

				if (this.cantel != '3') {
					$('#wxdiv').hide();
					$('#teldiv').show();
					if (tel) {
						$('#telinput').val(tel);
					}
					$('#hhshow').show();
				}

			} else if (ask == 'wx') {

				if (this.canwx != '3') {
					$('#teldiv').hide();
					$('#wxdiv').show();
					if (wxid) {
						$('#wxinput').val(wxid);
					}
					$('#hhshow').show();
				}
			}
		},
		refuseAsk:function(ask,chatid) {

			if (toid != '') {

				$.post(weburl + "/index.php?m=chat&c=refuseAsk", {
					toid: toid,
					totype: totype,
					ask: ask,
					chatid: chatid
				}, function(data) {

					var data = eval('(' + data + ')');

					if (data.error == 1) {

						huhuanStatus(ask, '2');
						sendMessage('', 'refuse', ask);
						banClickHtml(chatid, 'refuse');
					} else {
						layer.msg('参数错误，请重试', 2, 8);
					}
				});
			} else {
				layer.msg('参数错误，请重试', 2, 8);
			}
		},
		copyMsg:function(askvalue) {

			$('#copytextid').attr('data-clipboard-text',askvalue);
			
			$('.copytext').click();
		},
		toShow:function (url) {
	
			if (url && url != '') {
				window.open(url);
			}
		},
		jobShow:function(id) {
			
			$('#joblist').addClass('none');
			if (jid != id && jid > 0) {
				if (mine.usertype == 1) {
					var change = '您更换了与招聘者沟通的职位';
				} else {
					var change = '您更换了与求职者沟通的职位';
				}
				// 发送消息
				sendMessage(change, 'change', '');
			}

			sendMessage('', 'job', id);
			// 设置当前沟通职位
			jid = id;
		},
		// 展示邀请详情
		inviteShow:function(id) {
			if (id && id != '') {
				if (mine.usertype == 1) {
					var url = weburl + '/member/index.php?index.php?c=invite';
				} else {
					var url = weburl + '/member/index.php?index.php?c=invite';
				}
				location.href = url;
			}
		},
		//播放语音
		playVoice:function(chatid, key) {
			var voiceurl = this.chatlog[key].voice;
			var audio = document.getElementById('voice_' + chatid);
			var voicestatus = this.chatlog[key].voicestatus;
			var onplay = false;
			var playMsgid = this.playMsgid;
			var palyedAudio = document.getElementById('voice_' + playMsgid);
			
			
			if (isIELowVision) {

				var head = document.head || document.getElementsByTagName('head')[0];
				var bgsound = document.getElementsByTagName('bgsound')[0];
				if (!bgsound) {
					var player = document.createElement('bgsound');

					player.src = '';

					head.appendChild(player);
				}

				var bgsound = document.getElementsByTagName('bgsound')[0];
				if (bgsound.src == '') {
					bgsound.src = voiceurl;
					onplay = true;
				} else {
					bgsound.src = '';
				}

			} else {
				if(palyedAudio){
					palyedAudio.pause(); // 暂停
					palyedAudio.currentTime = 0;
				}
				if (audio !== null && chatid!=playMsgid) {
					if (audio.paused) {

						audio.play(); // 播放 

						onplay = true;

					} else {
						audio.pause(); // 停止
						audio.currentTime = 0;
					}
				}

				audio.addEventListener("play", function() { //开始播放时触发
					//console.log('开始播放');
				});
				
				audio.addEventListener("ended", function() {
					//console.log('播放结束');
					this.playMsgid = null;
				}, false);
			}
			if (onplay) {
				this.playMsgid = chatid;
			} else {
				this.playMsgid = null;
			}
			
			if (chatid && voicestatus == '0' && typeof isAdmin === 'undefined') {
				$.post(weburl + "/index.php?m=chat&c=setVoiceStatus", {
						chatid: chatid,
						id: toid,
						type: totype,
					},
					function(data) {
						if (data) {
							var res = eval('(' + data + ')');
							
							this.chatlog[key].voicestatus = 1;

							if (res.errmsg) {
								layermsg(res.errmsg, 2, function() {
									location.href = weburl;
									return;
								});
							}
						}
					}
				);
			}

		},
		// 打开DIV
		openExpectDiv() {
			this.expectOpen = !this.expectOpen;
			this.changeChatlist(2);
		},
		// 显示简历
		showExpect(uid){
			let that = this;
			let info = this.getExpectStorage(uid);
			if (info && info.uid) {
				this.expectInfo = info;
			} else {
				$.post(weburl + "/index.php?m=chat&c=resumeExpect", {
						uid: uid
					},
					function(data) {
						if (data) {
							var res = eval('(' + data + ')');

							if (res.error > 0) {
								console.log(res.msg);
							} else {
								that.expectInfo = res.data;
								that.expectStorage.push(res.data);
							}
						}
					}
				);
			}
		},
		// 获取简历缓存信息
		getExpectStorage(uid) {
			let info = {};
			if (this.expectStorage.length > 0) {
				this.expectStorage.forEach(function(item, index) {
					if (uid == item.uid) {
						info = item;
					}
				})
			}
			return info;
		},
		// 修改聊天列表窗口开启或关闭状态
		changeChatlist(type){
			let that = this;
			if (this.chatlist.length > 0) {
				this.chatlist.forEach(function(item, index) {
					if (that.toid == item.id) {
						if (type == 2) {
							that.chatlist[index].expectOpen = that.expectOpen; // 直接点击打开/关闭的处理
						} else {
							// 本身没有说明是首次打开 或者 切换聊天窗口
							if (typeof (that.chatlist[index].expectOpen) == 'undefined') {
								that.chatlist[index].expectOpen = true;
								that.expectOpen = true;
							} else {
								that.expectOpen = that.chatlist[index].expectOpen;
							}
						}
					}
				})
			}
		},
		com_lookresume_check(eid,resume_status,state){
			var param = {from:'webchat'};
			com_lookresume_check(eid, resume_status,state,param)
		}
	}
});



var hiddenProperty = 'hidden' in document ? 'hidden' : 'webkitHidden' in document ? 'webkitHidden' : 'mozHidden' in document ? 'mozHidden' : null;
// 不同浏览器的事件名
if (hiddenProperty) {
	var visibilityChangeEvent = hiddenProperty.replace(/hidden/i, 'visibilitychange');
}
localStorage.removeItem("socketNewchat");

var pageTitle = document.title;
var onVisibilityChange = function() {
	
	if (typeof isAdmin === 'undefined') {
		if(document[hiddenProperty]){
			
			// 关闭响铃
			if(spviewBell){
				spviewBell.pause();
				spviewBell = null;
			}
		}else{
			if (!socket || (socket && socket.readyState != 1)) {
				webSocket(socketUrl);
				chatOnShow();
			}
			document.title = pageTitle;
			clearInterval(titledsq);
			titledsq = null;
		}
	}
}
document.addEventListener(visibilityChangeEvent, onVisibilityChange);

//浏览器内部通过storage通讯
window.addEventListener("storage", function(e) {
	if (e.key == 'isspview' && e.newValue == '2' && spconfirm) {
		// 一个页面接受或拒绝视频面试邀请，请他页面的选择框关闭
		layer.close(spconfirm);
		commentState = null;
		if(spint){
			clearInterval(spint);
			spint = null;
		}
	}
})
//浏览器内部通过storage通讯
window.addEventListener("storage", function(e) {
	// 其他页面退出登录本页面需要关闭socket
	if (e.key == 'socketState' && e.newValue == '2') {
		if(socket && socket.readyState == 1){
			socket.close();
		}
	}
});

window.addEventListener("storage", function(e) {
	
	if (e.key == 'socketNewchat') {
		
		var unreadchat_id = e.newValue ? JSON.parse(e.newValue) : [];
		
		if (unreadchat_id.length>0) {
			if (document[hiddenProperty]) {
				titAn = true;
				titledsq = setInterval(function() {
					if (titAn) {
						document.title = '【新消息】';
						titAn = false;
					} else {
						document.title = $('#zwf').text();
						titAn = true;
					}
				}, 1000);
			}
		}else{
			document.title = pageTitle;
			clearInterval(titledsq);
			titledsq = null;
		}
		
	}
});

$(function() {
	isIELowVision = IEVersion();

	//获取所有聊天用户数据缓存
	if(typeof(isAdmin) === 'undefined'){
		getAllChatOnlineData(toid);
	}
	if ("WebSocket" in window && typeof(isAdmin) === 'undefined') {
		webSocket(socketUrl);
		chatOnLoad();
	} else {
		console.log("您的浏览器不支持聊天!");
	}
	// 发送消息
	if ($('#send').length > 0) {
		$('#send').click(function() {
			if (socket && socket.readyState == 1) {
				send();
			} else {
				webSocket(socketUrl);
				var timer = setInterval(function() {
					if (socket && socket.readyState == 1) {
						clearInterval(timer);
						send();
					}
				}, 500);
				setTimeout(function() {
					if (timer) {
						clearInterval(timer);
					}
				}, 10000);
			}
		});
	}
	if ($('#send_content').length > 0) {
		$('#send_content').on('keydown', function(e) {
			var keyCode = e.keyCode;
			if (keyCode === 13) {
				e.preventDefault();
				if (socket && socket.readyState == 1) {
					send();
				} else {
					webSocket(socketUrl);
					var timer = setInterval(function() {
						if (socket && socket.readyState == 1) {
							clearInterval(timer);
							send();
						}
					}, 500);
					setTimeout(function() {
						if (timer) {
							clearInterval(timer);
						}
					}, 10000);
				}
			}
		});
	}
	
	$("body").click(function(e) {
		var con = $("#commonly"); // 设置目标区域
		var face = $("#face");
		var usefulButton = $("#usefulButton");
		if ((!con.is(e.target) && con.has(e.target).length === 0) 
			&& (!face.is(e.target) && face.has(e.target).length ===0)) {
			commonHide();
		}
		if(!usefulButton.is(e.target) && usefulButton.has(e.target).length ===0){

			closeUseful();
		}
		var zdconButton = $("#zdconButton");
		if(!zdconButton.is(e.target) && zdconButton.has(e.target).length ===0){
			
			closeZdcon();
		}
	});
	$("#face").click(function() {
		face();
	});
	jsPrepare();
	if($('.copytext').length>0){
		//复制文本
		var clipboard = new ClipboardJS(".copytext");
	    clipboard.on('success', function (e) {
	        layer.msg('复制成功！');
	        e.clearSelection();
	    });
	    clipboard.on('error', function (e) {
	    });
	}
});

// js点击监听
function jsPrepare(){
	
	// 发送职位
	$('#closeJob').click(function() {
		$('#joblist').addClass('none');
	});
	
	// 视频面试
	$('#spview').click(function() {
		window.localStorage.setItem("spviewinvite", "1");
		var url = weburl + '/member/index.php?c=spview&act=webrtc&roomer=1&fuid=' + toid;
		if(jid != ''){
			url += '&jid=' + jid;
		}
		window.location.href = url;
	});
}
function webSocket(socketUrl) {
	if(islink == 1){
		if (ping_timer) {
			clearInterval(ping_timer);
			ping_timer = null;
		}
		socket = new WebSocket(socketUrl);
		socket.onopen = function() {
			var ping = {
				type: 'ping'
			};
			ping_timer = setInterval(function() {
				if (socket){
					if( socket.readyState == 1){
						socket.send(JSON.stringify(ping));
					}else{
						socket.close();
					}
				}
			}, 50000);
		};
		
		socket.onerror = function() {
			socket = null;
			islink = 2;
			if (ping_timer) {
				clearInterval(ping_timer);
				ping_timer = null;
			}
		};
		
		socket.onclose = function(res) {
			if (ping_timer) {
				clearInterval(ping_timer);
				ping_timer = null;
				if(res.code == 1006){
					// 服务器断开的重连
					webSocket(socketUrl);
					chatOnShow();
				}
			}
		};
		//捕捉socket端发来的事件
		socket.onmessage = function(event) {
			var e = JSON.parse(event.data);
			
			switch (e.type) {
				//显示收到的消息
				case 'getMessage':
					getMessage(e.data);
					break;
				case 'getUnread':
					if ($('#memberNoChat').length > 0) {
						getUnread();
					}
					break;
				case 'unsend':
				
					unSend(e.data);
					break;
				case 'error':
					islink = 2;
					if (ping_timer) {
						clearInterval(ping_timer);
						ping_timer = null;
					}
					break;
				case 'comment':
					comment(e.data);
					break;
				case 'closesp':
					closesp(e.data);
					break;
				case 'closeSpConfirm':
					closeSpConfirm(e.data);
					break;
				case 'getListOnline':
					onGetListOnline(e.data);
					break;
			}
		};
	}
}
function zdconTrigger(){
	if($('#zdconBox').hasClass('none')){
		showZdcon();
	}else{
		closeZdcon();
	}
}
function showZdcon(){
	$('#zdconBox').removeClass('none');
}
function closeZdcon(){
	$('#zdconBox').addClass('none');
}
function zdconDefConfig(){

	closeZdcon();
}
function zdconSetShow(){

	$.layer({
        type: 1,
        title: '离线自动回复',
        closeBtn: [0, true],
        border: [10, 0.3, '#000', true],
        area: ['auto', 'auto'],
        page: {
            dom: "#zdconSetBox"
        }
    });
}

function zdSwitch(){

	var cmsg = '打开后，离线状态收到消息将根据对方内容自动回复，是否确认？';
	
	var zd_switch = chatapp.$data.zd_switch;
	if(chatapp.$data.zdconlist.length<=0){
		layer.msg('请先添加自动回复', 2, 8);return false;
	}
	if(zd_switch=='1'){
		cmsg = '关闭后，离线状态不会自动回复，是否确认？';
	}

	var zdconfirm = layer.confirm(cmsg, {btn: ['确认', '算了']},
		function() {
			layer.close(zdconfirm);
			$.post(weburl + "/index.php?m=chat&c=zdSwitch",{zd_switch: zd_switch}, function(res){
				if (res.errcode == 9) {
					layer.msg(res.msg, 2, 9,function(){
						
						chatapp.$data.zd_switch = res.switch;
					});
				} else {
					layer.msg(res.msg, 2, 8);
				}
			},'json');
		},
		function() {
			layer.close(zdconfirm);
		}
	);
	
}
function editZdconClose(){
	$('#zdconcon').val('');
	$('#zdconkeyword').val('');
	$('#zdconsort').val('');
	$("#zdcontype").prop("checked",false);;
	layer.closeAll();
}
// 常用语保存
function zdconSave(){

	var zdconlist = deepClone(chatapp.$data.zdconlist);

	var content = $("#zdconcon").val();
	var zdconkeyword = $("#zdconkeyword").val();
	var zdconsort = $("#zdconsort").val();

	var zdcontype = $("#zdcontype").prop("checked")?1:0;
	
	if(content.trim().length == 0){
		layer.msg('请填写自动回复内容', 2, 8);return false;
	}
	if(content.trim().length > 200){
		layer.msg('自动回复字数最多200个', 2, 8);return false;
	}
	if(zdconkeyword.trim().length == 0){
		layer.msg('请填写关键字', 2, 8);return false;
	}
	var zdconid = $("#zdconid").val();
	
	var loadi = loadlayer();
	$.post(weburl + "/index.php?m=chat&c=zdconSave", {
		content: content,
		zdconkeyword: zdconkeyword,
		id: zdconid,
		type:zdcontype,
		sort:zdconsort
	}, function(res) {
		
		layer.closeAll();
		if(res.errcode == 9){
			layer.msg(res.msg, 2, 9,function(){
				
				if(zdconid!=''){
				
					for(var i in zdconlist){
						if(zdconlist[i].id == zdconid){
							zdconlist[i].content = content;
							zdconlist[i].keyword = zdconkeyword;
							zdconlist[i].type = zdcontype;
							zdconlist[i].sort = zdconsort;
						}
					}
					chatapp.$data.zdconlist = zdconlist;
					
				}else{
					
					var zdcon = {
						content:content,
						id:res.id,
						uid:mine.id,
						keyword:zdconkeyword,
						sort:zdconsort,
						type:zdcontype,
					};
					zdconlist.unshift(zdcon);
					chatapp.$data.zdconlist = zdconlist;
					
				}
				zdconDefConfig();
			});
		}else{
			layer.msg(res.msg, 2, 8);
		}
	}, 'json');
}
function UsefulTrigger(){
	if($('#usefulBox').hasClass('none')){
		showUseful();
	}else{
		closeUseful();
	}
}
function showUseful(){

	$('#usefulBox').removeClass('none');
}
function closeUseful(){
	
	$('#usefulBox').addClass('none');
}
//常用语初始化，默认窗体隐藏，内容为空
function usefulDefConfig(){

	closeUseful();
	editUsefulClose();
	
	
}
function usefulSetShow(){

	$.layer({
        type: 1,
        title: '常用语设置',
        closeBtn: [0, true],
        border: [10, 0.3, '#000', true],
        area: ['auto', 'auto'],
        page: {
            dom: "#usefulSetBox"
        }
    });
}
function editGreeting(){

	var greeting = chatapp.$data.greeting;
	var greeting_id = chatapp.$data.greeting_id;

	$('#usefulid').val(greeting_id);
	$('#usefulcon').val(greeting);
	$('#usefultype').val(1);
	$('#usefulAddBox').show();
}

function editUsefulClose(){
	$('#usefultype').val('');
	$('#usefulid').val('');
	$('#usefulcon').val('');
	$('#usefulAddBox').hide();
}
// 常用语保存
function usefulSave(){

	var usefullist = deepClone(chatapp.$data.usefullist);

	var content = $("#usefulcon").val();

	var usefultype = $("#usefultype").val();
	var type_n = '常用语';
	if(usefultype==1){
		type_n = '问候语';
	}

	if(content.trim().length == 0){
		layer.msg('请填写'+type_n, 2, 8);return false;
	}
	if(content.trim().length > 200){
		layer.msg(type_n+'字数最多200个', 2, 8);return false;
	}
	var usefulid = $("#usefulid").val();
	
	var loadi = loadlayer();
	$.post(weburl + "/index.php?m=chat&c=usefulSave", {
		content: content,
		id: usefulid,
		type:usefultype
	}, function(res) {
		layer.closeAll();
		if(res.errcode == 9){
			layer.msg(res.msg, 2, 9,function(){
				
				if(usefulid!=''){
					if(usefultype==1){
						chatapp.$data.greeting = content;
					}else{
						for(var i in usefullist){
							if(usefullist[i].id == usefulid){
								usefullist[i].content = content;
							}
						}
						chatapp.$data.usefullist = usefullist;
					}
					
				}else{
					if(usefultype==1){
						chatapp.$data.greeting = content;
						chatapp.$data.greeting_id = res.id;
					}else{
						var userful = {
							content:content,
							id:res.id,
							uid:mine.id
						};
						usefullist.unshift(userful);
						chatapp.$data.usefullist = usefullist;
					}
				}
				usefulDefConfig();
			});
		}else{
			layer.msg(res.msg, 2, 8);
		}
	}, 'json');
}

// 加载聊天数据
function chatOnLoad() {
	$.post(weburl + "/index.php?m=chat&c=goChat", {
		rand: Math.random()
	}, function(data) {
		if (data) {
			var res = eval('(' + data + ')');
			mine = res.mine;

			chatapp.$data.usefullist = res.usefullist;
			usefulDefConfig();

			chatapp.$data.zdconlist = res.zdconlist;
			zdconDefConfig();

			tel = mine.tel;
			wxid = mine.wxid;
			if (socket && socket.readyState == 1) {
				bindUser(res)
			} else {
				var t = setInterval(function() {
					if (socket && socket.readyState == 1) {
						clearInterval(t);
						t = null;
						bindUser(res);
					}
				}, 100);
				setTimeout(function() {
					if (t) {
						clearInterval(t);
						t = null;
					}
				}, 10000);
			}
			// 让其他页面检查未读消息
			if (toid != '') {
				var jw = setInterval(function() {
					if (socket && socket.readyState == 1) {
						clearInterval(jw);
						jw = null;
						var message = {
							type: 'getUnread',
							data: {
								uid: mine.id,
							}
						}
						socket.send(JSON.stringify(message));
					}
				}, 1000);
				setTimeout(function() {
					if (jw) {
						clearInterval(jw);
						jw = null;
					}
				}, 10000);
			}
		}
	});
}
// 重新连接请求数据
function chatOnShow() {
	$.post(weburl + "/index.php?m=chat&c=getToken", {
		rand: Math.random()
	}, function(data) {
		if (data) {
			var res = eval('(' + data + ')');
			if (socket && socket.readyState == 1) {
				bindUser(res);
			} else {
				var t = setInterval(function() {
					if (socket && socket.readyState == 1) {
						clearInterval(t);
						t = null;
						bindUser(res);
					}
				}, 500);
				setTimeout(function() {
					if (t) {
						clearInterval(t);
						t = null;
					}
				}, 10000);
			}
		}
	})
}

function bindUser(res) {
	if (res.yuntoken && res.yuntoken.token) {
		var yuntoken = res.yuntoken.token;
		var bind = {
			type: 'bind',
			data: {
				uid: mine.id,
				yuntoken: yuntoken
			}
		}
		socket.send(JSON.stringify(bind));
		canSend = true;
		window.localStorage.setItem("socketState", "1");
		
		if($("#zphnet_group").length > 0){
			// 网络招聘会详细页
			if(lineList.length > 0){
				var msg = {
					type: 'getListOnline',
					data: {
						mine: {
							id: mine.id,
							list: lineList,
							timestamp: new Date().getTime()
						}
					}
				};
				socket.send(JSON.stringify(msg));
				lineInt = setInterval(function(){
					sendGetonline();
				},1000 * 60);
			}
		}
	} else {
		socket = null;
		islink = 2;
		if(ping_timer){
			clearInterval(ping_timer);
			ping_timer = null;
		}
	}
}

function checkSend() {
	var content = document.getElementById('send_content').value;
	if (content.trim() != '') {
		$('#send').removeClass('disclick');
	} else {
		$('#send').addClass('disclick');
	}
}

function back() {
	history.back();
}

function getPrepare() {
	var loadi = setInterval(function(){
		if(typeof layer != 'undefined' && ! moreReady){
			clearInterval(loadi);
			layer.load();
		}
	},20);
	var loadci = setInterval(function(){
		if(moreReady){
			clearInterval(loadci);
			layer.closeAll('loading');
		}
	},1000);
	$.post(weburl + "/index.php?m=chat&c=prepare", {
		'toid': toid,
		'totype': totype
	}, function(data) {
		if (data) {
			var res = eval('(' + data + ')');
			if (res.joblist) {
				chatapp.$data.joblist = res.joblist;
			}
			if (res.expect) {
				chatapp.$data.expect = res.expect;
			}
			// 渲染聊天记录
			moreChat();
			
		}
	});
}

// 发送消息
function send() {
	var content = document.getElementById('send_content');
	if (content.value.trim() != '') {
		// 发送聊天
		if (toid != '') {
			sendMessage(content.value, 'char', '');
			content.value = '';
			checkSend();
		} else {
			layer.msg('请选择' + chat_name + '对象', 2, 8);
		}
	}
}
// 更新聊天列表的聊天内容
function setChatDesc(msg){
	if (msg.ftype == '1' || msg.ftype == '2') {
		var content = msg.content;
		var chatlist = deepClone(chatapp.$data.chatlist);
		if (msg.msgtype == 'char') {
			if (content.indexOf('img[') > -1) {
				content = '[图片消息]';
			}else if (content.indexOf('face[') > -1) {
				content = '[表情消息]';
			}
		} else if (msg.msgtype == 'voice') {
			content = '[语音消息]';
		} else if (msg.msgtype == 'ask') {
			content = '[请求互换联系方式]';
		} else if (msg.msgtype == 'confirm' || msg.msgtype == 'refuse') {
			content = '[互换联系方式消息]';
		} else if (msg.msgtype == 'job') {
			content = '[职位消息]';
		} else if (msg.msgtype == 'resume') {
			content = '[简历消息]';
		} else if (msg.msgtype == 'invite') {
			content = '[面试消息]';
		} else if (msg.msgtype == 'map') {
			content = '[位置]';
		} else if (msg.msgtype == 'read') {
			return '';
		} else if (msg.msgtype == 'recall') {
			return '';
		}
		//$('#chat_desc_' + msg.id).html(content);
		for(let i in chatlist){
			if(chatlist[i].id==msg.id){
				chatlist[i].content = content;
			}
		}
		chatapp.$data.chatlist = chatlist;
		return content;
	}
}

function getMessage(msg) {
	if (msg.id && msg.totype == mine.usertype) {

		var chatStorage = deepClone(chatapp.$data.chatStorage);
	
		var setArr = ['char', 'job', 'resume', 'invite', 'ask', 'confirm', 'refuse', 'spview','voice'];
		
		if(setArr.indexOf(msg.msgtype) != -1 && typeof chatStorage[msg.id]!='undefined' && chatStorage[msg.id]){
			if(msg.msgtype == 'ask'){
				msg.askstatus = 3;
			}else if(msg.msgtype == 'confirm'){
				msg.askstatus = 1;
			}else if(msg.msgtype == 'refuse'){
				msg.askstatus = 2;
			}
			chatStorage[msg.id].history.unshift(msg);
			chatapp.$data.chatStorage = chatStorage;
		}

		var chatlist = deepClone(chatapp.$data.chatlist);

		if (toid != '' && msg.id == toid) {

			for(let i in chatlist){
				if(chatlist[i].id == msg.id){
 					chatlist[i].timestamp = msg.timestamp;
 					chatlist[i].time = timeFormat(msg.timestamp);
				}
			}

			chatapp.$data.chatlist = sortByField(chatlist,'timestamp','desc');

			if(msg.msgtype == 'adminjob'){
				getAdminjob(msg);
			}else{
				
				chatRender(msg, 'get');
				
				setRead(msg);
			}
			setChatDesc(msg);
		} else {

			
			var key = '';
			for(let i in chatlist){
				if(chatlist[i].id == msg.id){
 					key = i;
				}
			}

			if (msg.msgtype == 'read') {
				return
			} else if (msg.msgtype == 'recall') {
				// 聊天人列表处理消息撤回
				if(msg.content.indexOf('recall') != -1){
					if(parseInt(chatlist[key].unread)>1){
						chatlist[key].unread = parseInt(chatlist[key].unread) - 1;
						var leftUnreadChatNum = $('#leftChatNum').html();
						if (leftUnreadChatNum == ''){
							leftUnreadChatNum = '';
						}else{
							leftUnreadChatNum = parseInt(leftUnreadChatNum) > 1 ? parseInt(leftUnreadChatNum) - 1 : '';
						}
						$('#leftChatNum').html(leftUnreadChatNum);
					}
					chatlist[key].timestamp = msg.timestamp;
					chatlist[key].time = timeFormat(msg.timestamp);
					var rn = '撤回了一条消息';
					if(msg.id == mine.id){
						rn = '你' + rn;
					}else if(msg.linkman){
						rn = '对方' + rn;
					}
					chatlist[key].content = rn;
					var recallid = msg.content.replace('recall=','');
					recallChat(msg.id, recallid, false);
					chatapp.$data.chatlist = sortByField(chatlist,'timestamp','desc');
					// 右下角浮窗处理
					if ($('#memberNoChat').length > 0) {
						var num = $('#memberChatNum').text();
						if (parseInt(num) > 1) {
							$('#memberChatNum').text(parseInt(num) - 1);
						}else{
							$('#memberChatNum').text(0);
							$('#memberNoChat').removeClass('none');
							$('#memberHaveChat').addClass('none');
						}
					}
				}
				return
			}
			
			if(unreadchat.indexOf(msg.id)==-1){
				unreadchat.push(msg.id);

				if(!document[hiddenProperty]){
					window.localStorage.setItem("socketNewchat",JSON.stringify(unreadchat));
				}else{

					titAn = true;
					titledsq = setInterval(function() {
						if (titAn) {
							document.title = '【新消息】';
							titAn = false;
						} else {
							document.title = $('#zwf').text();
							titAn = true;
						}
					}, 1000);
					
				}
				
			}
			
			if(key!=''){
				if(parseInt(chatlist[key].unread)>0){
					var num = parseInt(chatlist[key].unread) + 1;
					chatlist[key].unread = num;
				}else{
					chatlist[key].unread = 1;
				}
				var leftUnreadChatNum = $('#leftChatNum').html();
				if (leftUnreadChatNum == ''){
					leftUnreadChatNum = 1;
				}else{
					leftUnreadChatNum = parseInt(leftUnreadChatNum) + 1;
				}
				$('#leftChatNum').html(leftUnreadChatNum);
				chatlist[key].timestamp = msg.timestamp;
				chatlist[key].time = timeFormat(msg.timestamp);
				chatapp.$data.chatlist = sortByField(chatlist,'timestamp','desc');
				setChatDesc(msg);
			}else{
				$.post(weburl + "/index.php?m=chat&c=getSingleChatOnlineData", {
					'toid': msg.id,
					'totype': msg.ftype,
				}, function(res) {
					
					chatStorage[msg.id] = res;
					if(chatStorage[msg.id].history.length==0){
						chatStorage[msg.id].history.unshift(msg);
					}
					chatapp.$data.chatStorage = chatStorage;
					if(res.lastjob_id){
						
						chatapp.$data.lastjob_arr[msg.id] = res.lastjob_id;
					}
					var chat = deepClone(msg);
					chat.tusertype =  msg.ftype;
					chat.time =  timeFormat(msg.timestamp);
					chat.unread =  1;
					chatapp.$data.chatlist.push(chat);
					chatapp.$data.chatlist = sortByField(chatapp.$data.chatlist,'timestamp','desc');
					
					setChatDesc(msg);
					
				},'json');
			}
			
			if ($('#memberNoChat').length > 0) {
				if ($('#memberHaveChat').hasClass('none')) {
					$('#memberNoChat').addClass('none');
					$('#memberHaveChat').removeClass('none');
					$('#memberChatNum').text(1);
					
				} else {
					var num = $('#memberChatNum').text();
					if (parseInt(num) > 0) {
						$('#memberChatNum').text(parseInt(num) + 1);

					}
				}
			}
		}
		
	}
}
// 由简历/职位详情页进入,发预定消息
function prepare() {
	
	if (jid != '' && chatapp.$data.joblist && chatapp.$data.joblist[jid] && chatapp.$data.jobShowed) {
		// 发送消息
		setTimeout(function() {
			sendMessage('', 'job', jid);
		}, 500);

		if(chatapp.$data.greeting_switch==1){
			setTimeout(function() {
				var send = chatapp.$data.greeting  ? chatapp.$data.greeting : '你好，可以聊聊吗？这个职位我很感兴趣，希望进一步了解';
				sendMessage(send, 'char', '');
			}, 800);
		}
		
	}
	if(usertype != 1 &&  chatapp.$data.chatlog.length==0 && chatapp.$data.greeting_switch==1){
		// 没有聊天记录，非个人用户向个人用户打招呼
		var send = chatapp.$data.greeting ? chatapp.$data.greeting : '你好，可以聊聊吗？';
		setTimeout(function() {
			sendMessage(send, 'char', '');
		}, 800);
	}
}

function sendExpcet() {
	var expect = deepClone(chatapp.$data.expect);
	if (expect) {
		
		sendMessage('', 'resume', expect.id);
	} else {
		var i = layer.confirm('您还没有可以投递的简历', {
				btn: ['前往发布', '取消']
			},
			function() {
				window.location.href = weburl + '/member/index.php?c=expect&act=add';
				return false;
			},
			function() {
				layer.close(i);
			}
		);
	}
}

function sendJobAddress() {
	var lastjob = deepClone(chatapp.$data.lastjob);
	if (lastjob) {
		if(lastjob.job_x && lastjob.job_y){

			if(lastjob.job_mappic==''){

				$.post(weburl + "/index.php?m=chat&c=staticMap", {
					lng: lastjob.job_x,
					lat: lastjob.job_y
				}, function(res) {
					layer.closeAll('loading');
					if (res.error=='1') {
						var content = lastjob.job_address + '|' + res.url + '|' + lastjob.job_y + '|' + lastjob.job_x;
						sendMessage(content, 'map','');
					}else{
						layer.msg(res.errmsg, 2, 8);
					}
				},'json');

			}else{
				var content = lastjob.job_address + '|' + lastjob.job_mappic + '|' + lastjob.job_y + '|' + lastjob.job_x;
				sendMessage(content, 'map','');
			}
		}else{
			sendMessage(lastjob.job_address, 'char','');
		}
		chatapp.$data.lastjobshow = false;
	} else {
		chatapp.$data.lastjob = null;
	}
}
function showlastjob(){
	chatapp.$data.lastjobshow = true;
}
function hidelastjob(){
	chatapp.$data.lastjobshow = false;
}

function sendInvite(id) {
	
	// 发送消息
	sendMessage('', 'invite', id);
}


function sendJob() {
	$('#joblist').removeClass('none');
}



function nochat() {
	layer.open({
		content: '设定不感兴趣将会解除双方好友关系，并且删除历史'+chat_name+'记录，确定继续？',
		btn: ['确定', '取消'],
		yes: function(index) {
			layer.close(index);
			loadlayer();
			$.post(weburl + "/index.php?m=chat&c=delChatLog", {
				to: toid,
				type: totype
			}, function(res) {
				layer.closeAll('loading');
				if (res.errmsg) {
					layer.msg(res.errmsg, 2, 8, function() {
						location.href = weburl;
					});
				} else {
					var chatlist = deepClone(chatapp.$data.chatlist);
					
					for(let i in chatlist){
						if(chatlist[i].id == toid){
							chatlist.splice(i, 1);
						}
					}
					chatapp.$data.chatlist = chatlist;
					chatapp.$data.expectInfo = {};
					$("#cbr_y").hide();
					$("#cbr_n").show();
					
					// var winname = '';
					
					// if (!(!!window.ActiveXObject || "ActiveXObject" in window)){
					// 	winname = 'chatbox';
					// }
					// window.open(weburl + '/member/index.php?c=chat',winname);
				}
			},'json');
		}
	});
}

// 发送消息
function sendMessage(content, type, id) {

	var time = new Date().getTime();
	var chatStorage = deepClone(chatapp.$data.chatStorage);
	var chatlist = deepClone(chatapp.$data.chatlist);

	if (socket && socket.readyState == 1 && canSend){
		// 根据消息类型区分保存内容
		if (type == 'job') {
			var msgcontent = 'jobid=' + id;
			showChatJob(id);
		} else if (type == 'resume') {
			var msgcontent = 'eid=' + id;
		} else if (type == 'invite') {
			var msgcontent = 'inviteid=' + id;
		} else if (type == 'ask') {
			var msgcontent = 'ask=' + id;
		} else if (type == 'confirm') {
			var msgcontent = 'confirm=' + id;
		} else if (type == 'refuse') {
			var msgcontent = 'refuse=' + id;
		} else if (type == 'read') {
			var msgcontent = 'read=' + id;
		} else if (type == 'recall') {
			var msgcontent = 'recall=' + id;
		} else if (type == 'map') {
			var msgcontent = content;
		} else {

			if((totype==2 || totype==3) && chatright!=1){
				//屏蔽手机号验证
				content = content.replace(/\s*/g,"");//去除字符串中所有空格
				var est = /\d{11}/g;
				var mreg = /^[1][3456789]\d{9}$/;
				var content2 = content.match(est);
				var tel_n = '';
				for(var i in content2){
					
					if(mreg.test(content2[i])==true){
						tel_n = content2[i].replace(/^(\d{3})\d{4}(\d{4})$/, "$1****$2");
						content = content.replace(content2[i],tel_n);
					}
				}
				//屏蔽手机号验证end
			}
			
			var msgcontent = content;

		}
		var mineData = {
			id: mine.id,
			mine: true,
			ftype: mine.usertype,
			avatar: mine.avatar,
			username: mine.username,
			linkman: mine.linkman,
			timestamp: time,
			content: msgcontent,
			msgtype: type,
			op: {},
		};
		if (type == 'confirm') {
			mineData.op.confirmcon = content;
		}
		var sendData = {
			mine: mineData,
			to: {
				id: toid,
				utype: totype
			}
		};
		var message = {
			type: 'chatMessage',
			data: {
				content: sendData
			}
		};
		// 保存聊天记录
		var nolog = ['change', 'read', 'recall'];
		if (nolog.indexOf(type) == -1) {

			var pdata = {
				to: sendData.to.id,
				totype: sendData.to.utype,
				content: msgcontent,
				timestamp: sendData.mine.timestamp,
				msgtype: sendData.mine.msgtype,
				nowid: sendData.mine.id,
				nowtype: sendData.mine.ftype
			};

			$.post(weburl + "/index.php?m=chat&c=chatLog", pdata, function(data) {
				if (data) {
					var res = eval('(' + data + ')');
					if (res.error != 0) {
						if (res.error == 4) {
							$('#chatid').val(sendData.to.id);
							server_single('chat');
							firstTab();
							var msglayer = layer.open({
								type: 1,
								title: chat_name,
								closeBtn: 1,
								border: [10, 0.3, '#000', true],
								area: ['810px', 'auto'],
								content: $("#tcmsg"),
								cancel: function() {
									window.location.reload();
								}
							});
							return false;
						}else if (res.error == 3) {
							layer.msg(res.errmsg, 2, 8);
						} else if (res.errmsg) {
							layer.msg(res.errmsg, 2, 8, function() {
								location.href = weburl;
								return;
							});
						}
					} else {
						message.data.content.mine.chatid = res.chatid;
						socket.send(JSON.stringify(message));
						// 渲染消息内容
						mineData.chatid = res.chatid;

						chatapp.$data.newchatid = res.chatid;
						// 渲染消息内容
						mineData.htmlcontent = content;

						if(typeof chatStorage[toid]!='undifined' && chatStorage[toid]){
							var newchat = deepClone(mineData);
							newchat.sendTime = time;
							for(let i in newchat.op){
								newchat[i] = newchat.op[i];
							}
							chatStorage[toid].history.unshift(newchat);
							chatapp.$data.chatStorage = chatStorage;
						}
						chatRender(mineData, 'send',id);
						// 发送后，聊天列表渲染消息
						for(let i in chatlist){
							if(chatlist[i].id == toid){
			 					chatlist[i].timestamp = time;
							}
						}

						chatapp.$data.chatlist = sortByField(chatlist,'timestamp','desc');
						mineData.id = toid;
						setChatDesc(mineData);
					}
				}
			});
		}else if(type == 'recall'){
			// 消息撤回
			loadlayer();
			$.post(weburl + "/index.php?m=chat&c=recall", {
				chatid: id,
				toid: toid,
				type: totype
			}, function(res) {
				layer.closeAll('loading');
				socket.send(JSON.stringify(message));
				recallChat(toid, id, true);
			},'json');
		}else {
			socket.send(JSON.stringify(message));
			// 渲染消息内容
			mineData.htmlcontent = content;
			chatRender(mineData, 'send',id);
		}
		
	} else {
		layer.msg(chat_name + '功能加载中', 2, 8);
	}
}
// 渲染消息内容
function chatRender(msg, type,id) {
	
	var chatStorage = deepClone(chatapp.$data.chatStorage);

	if (type == 'send') {
		// 发送
		var chat = {
			mine: true,
			avatar: msg.avatar,
			msgtype: msg.msgtype,
			read: 0,

		};

		if (msg.msgtype == 'char'){
			if(msg.content.indexOf('img[') != -1){
			 	chat.image = msg.content.replace(/(^img\[)|(\]$)/g, '');
			}else{
				chat.content = rexContent(msg.content);
			}
		}else if (msg.msgtype == 'job'){
			chat.jobid = id;
			if(chatapp.$data.joblist[id]){
				chat.job = chatapp.$data.joblist[id];
				chat.ctime = timeFormat(msg.timestamp);

				if(mine.usertype=='2'){
					chatapp.$data.lastjob_arr[toid] = id;
					chatapp.$data.lastjob = chatapp.$data.joblist[id];
				}
			}
			jid = id;
		}else if(msg.msgtype == 'resume'){
			chat.eid = id;
		}else if(msg.msgtype == 'map'){
			// 位置消息
			let map = msg.content.split('|');
			if(map.length == 4){
				chat.name = map[0];
				chat.url = map[1];
				chat.lat = map[2];
				chat.lng = map[3];
			}
		}else if(msg.msgtype == 'invite'){
			// 邀请面试消息和普通消息类似
			chat.msgtype = 'char';
			chat.inviteid = id;
		}else if (msg.msgtype == 'ask') {
			chat.asktype = id;
			chat.asktext = getAskContent(chat);
		} else if (msg.msgtype == 'confirm') {

			chat.confirmtype = id;
			if(id=='wx'){
				chat.askvalue = chatapp.$data.towx;
				chat.askmsg	  = chatapp.$data.linkman+'的微信号:';
			}else if(id=='tel'){
				chat.askvalue = chatapp.$data.totel;
				chat.askmsg	  = chatapp.$data.linkman+'的手机号:';
			}
			huhuanStatus(id,1);
		} else if (msg.msgtype == 'refuse') {
			chat.refusetype = id;
			chat.refusetext = getRefuseContent(chat);
			huhuanStatus(id,2);
		} else if (msg.msgtype == 'read') {
			// 发送已读，自己不用渲染
			return
		}  else if (msg.msgtype == 'recall') {
			// 发送撤回消息，自己不用渲染
			return
		}

		
	} else {
		if(msg.msgtype == 'char' && msg.content.indexOf('img[') == -1){
			var content = rexContent(msg.content);
		}else{
			var content = msg.content;
		}
		var	chat = {
			    chatid: new Date().getTime(),
				mine: false,
				msgtype: msg.msgtype,
				avatar: msg.avatar,
				content: content
			};
		// 接收
		if (msg.msgtype == 'job'){
			if(content.indexOf('jobid') != -1){
				var jobid = content.replace('jobid=','');
				if(chatapp.$data.joblist[jobid]){
					chat.job = chatapp.$data.joblist[jobid];
					chat.ctime = timeFormat(msg.timestamp);

					if(mine.usertype=='2'){
						chatapp.$data.lastjob_arr[toid] = jobid;
						chatapp.$data.lastjob = chatapp.$data.joblist[jobid];
					}
				}
				jid = jobid;
			}
		}else if (msg.msgtype == 'resume'){
			if(content.indexOf('eid') != -1){
				chat.eid = content.replace('eid=','');
			}
		}else if(msg.msgtype == 'voice'){
			if(content.indexOf('voice[') != -1){
				chat.voice = content.replace(/(^voice\[)|(\]$)/g, '');
				chat.voicestatus= 0;
			}
			chat.voicelength = msg.voicelength;
			
		}else if (msg.msgtype == 'ask'){
			if(content.indexOf('ask') != -1){
				chat.asktype = content.replace('ask=','');
				chat.asktext = getAskContent(chat);
				chat.askstatus = 3;
			}
		}else if (msg.msgtype == 'confirm'){
			if(content.indexOf('confirm') != -1){
				var confirmid = content.replace('confirm=','');
				chat.confirmtype = confirmid;
				chat.askvalue = msg.confirmcon;
				if(confirmid == 'wx'){
					var asktype = '的微信号:';
					chatapp.$data.canwx = 1;
				}else if(confirmid == 'tel'){
					var asktype = '的手机号:';
					chatapp.$data.cantel = 1;
				}
				chat.askmsg   = chatapp.$data.linkman + asktype;
			}
		}else if(msg.msgtype == 'refuse'){
			
			if(content.indexOf('refuse') != -1){
				var refuseid = content.replace('refuse=','');
				chat.refusetype = refuseid;
				if(refuseid == 'wx'){
					chatapp.$data.canwx = 2;
				}else if(refuseid == 'tel'){
					chatapp.$data.cantel = 2;
				}
				chat.refusetext = getRefuseContent(chat);
			}
		}else if (msg.msgtype == 'spview'){
			
		}else if (msg.msgtype == 'read'){
			if(content.indexOf('read') != -1){
				var chatlog = deepClone(chatapp.$data.chatlog);
				for(var i in chatlog){
					if(chatlog[i].read == 0){
						chatlog[i].read = 1;
					}
				}
				chatapp.$data.chatlog = chatlog;

			}
			
			return
		}else if (msg.msgtype == 'recall'){
			if(content.indexOf('recall') != -1){
				var recallid = content.replace('recall=','');
				recallChat(msg.id, recallid, false);
				
			}
			return
		}else if(msg.msgtype == 'invite'){
			// 邀请面试消息和普通消息类似
			if(content.indexOf('inviteid') != -1){
				chat.inviteid = content.replace('inviteid=','');
				chat.msgtype = 'char';
			}
		}else if(msg.msgtype == 'map'){
			// 位置消息
			let map = content.split('|');
			if(map.length == 4){
				chat.name = map[0];
				chat.url = map[1];
				chat.lat = map[2];
				chat.lng = map[3];
			}
		}else{
			if(content.indexOf('img[') != -1){
			 	chat.image = content.replace(/(^img\[)|(\]$)/g, '');
			}
		}
		if (msg.timestamp - sendTime > 60 * 1000) {
			chat.time = timeFormat(msg.timestamp);
			sendTime = msg.timestamp;
		}
		
	}
	chat.sendTime=msg.timestamp;
	if(msg.chatid){
		chat.chatid = msg.chatid;
	}
	chatapp.$data.chatlog.push(chat);
	
	chatapp.$nextTick(function(){
		var scroll = document.getElementById('chat_content').scrollHeight;
		chatScrollTo(scroll);
		// 图片类型加载较慢，滚动到底部需要时间
		if ($("#chat_content").find('ul').last().find('img')) {
			// 上面滚动过之后，新的高度
			lastscroll = document.getElementById('chat_content').scrollHeight;
			var timer = setInterval(function() {
				var imgscroll = document.getElementById('chat_content').scrollHeight;
				if (imgscroll > lastscroll) {
					chatScrollTo(imgscroll);
					clearInterval(timer);
					timer = null;
				}
			}, 200);
			setTimeout(function() {
				if (timer) {
					clearInterval(timer);
				}
			}, 2000);
			picviewjs();
		}
	})
	
}

// 转义内容
function rexContent (content){

	content = (content||'').replace(/&(?!#?[a-zA-Z0-9]+;)/g, '&amp;')
	.replace(/face\[([^\s\[\]]+?)\]/g, function(face){  //转义表情
		var alt = face.replace(/^face/g, '');
		return '####' + faces([alt]) + '####';
	});
	var carr = content.split('####');
	var nc = [];
	for(var i in carr){
		if(carr[i].trim() != ''){
			if(carr[i].indexOf(weburl + '/js/im/emoji_') > -1){
				nc.push({t:'emoji',n:carr[i]});
			}else{
				nc.push({t:'char',n:carr[i]});
			}
		}
	}
	return nc;
}

function upImage(obj) {
	layer.load();
	$('#imgform').submit();
	var iframe = $('#chat_iframe'),
		timer = setInterval(function() {
			var res;
			try {
				res = iframe.contents().find('body').text();
			} catch (e) {
				layer.closeAll('loading');
				clearInterval(timer);
				timer = null;
			}
			if (res) {
				layer.closeAll('loading');
				clearInterval(timer);
				timer = null;
				iframe.contents().find('body').html('');
				try {
					res = JSON.parse(res);
				} catch (e) {
					res = {};
				}
				if (res.msg) {
					layer.msg(res.msg, 2, 8);
				} else {
					// 保存输入框内原本字符
					var content = document.getElementById('send_content');
					var oldstr = content.value;
					content.value = '';
					
					focusInsert(document.getElementById("send_content"), 'img[' + (res.data.url || '') + ']', true);
					send();
					// 发送图片后，将原本字符加回去
					setTimeout(function(){
						if (oldstr != '' && oldstr.trim() != '') {
							focusInsert(document.getElementById("send_content"), oldstr, true);
						}
					},500);
				}
			}
		}, 30);
	obj.value = '';
}

function timeFormat(nowtime) {
	if (nowtime) {
		nowtime = new Date(nowtime);
	} else {
		nowtime = new Date();
	}
	var month = nowtime.getMonth(),
		day = nowtime.getDate(),
		hour = nowtime.getHours(),
		minutes = nowtime.getMinutes();
	month = month + 1;
	if (month < 10) {
		month = '0' + month;
	}
	if (hour < 10 && hour > 0) {
		hour = '0' + hour;
	}
	if (minutes < 10) {
		minutes = '0' + minutes;
	}
	if (day < 10 && day > 0) {
		day = '0' + day;
	}
	var time = month + '-' + day + ' ' + hour + ':' + minutes;
	return time;
}
// 页面滚动
function chatScrollTo(ypos) {
	var clientHeight = document.getElementById('chat_content').clientHeight;;
	if (ypos > clientHeight) {
		// 兼容各种浏览器
		document.getElementById('chat_content').scrollTop = ypos;
	}
}
//在焦点处插入内容
function focusInsert(obj, str, nofocus) {
	var result, val = obj.value;
	nofocus || obj.focus();
	if (document.selection) { //ie
		result = document.selection.createRange();
		document.selection.empty();
		result.text = str;
	} else {
		result = [val.substring(0, obj.selectionStart), str, val.substr(obj.selectionEnd)];
		nofocus || obj.focus();
		obj.value = result.join('');
	}
}

function moreChat() {

	var chatStorage = deepClone(chatapp.$data.chatStorage);

	$.ajax({
		type: "post",
		url: weburl + "/index.php?m=chat&c=getChatPage",
		async: false,
		data: {
			'id': toid,
			'totype': totype,
			'page': chatpage,
			'lastid': lastid
		},
		dataType: "json",
		success: function(res) {
			layer.closeAll('loading');
			if (res) {
				if(res.msg){
					return layer.msg(res.msg, 2, 8, function(){
						window.location.href = weburl + '/member/';
					});
				}
				chatpage = chatpage + 1;
				
				var scroll = null;
				if (chatpage > 2) {
					// 第二页开始，记录前一次滚动距离
					scroll = document.getElementById('chat_content').scrollHeight;
				}
				
				if (res.code == 0) {
					var list = [];
					if(res.adminjob){
						adminjob = res.adminjob;
					}
					var chatlog = res.data;
					if (chatlog.length > 0) {
						
						pastSend = chatlog[0].sendTime;
						chatapp.$data.morelog = false;

						for (var i in chatlog) {
							if(chatStorage[toid]){
								chatStorage[toid].history.push(chatlog[i]);
								
							}

							chat = renderPast(chatlog[i],i);
							if(chat){
								list.push(chat);
							}
							// 最后一条时，如还有历史消息，增加更多消息
							if (i == chatlog.length - 1 && res.ismore) {
								chatapp.$data.morelog = true;
								lastid = chatlog[i]['id'];
							}
						}
						

						chatapp.$data.chatStorage = chatStorage;
						list.reverse();
						chatapp.$data.chatlog = list.concat(chatapp.$data.chatlog);
						
						if (scroll) {
							chatapp.$nextTick(function(){
								// 渲染后，滚动到上一页的距离
								var newScroll = document.getElementById('chat_content').scrollHeight;
								chatScrollTo(newScroll - scroll);
							})
						}
					}else{
						chatapp.$data.morelog = false;
					}
				}
				prepare();
				moreReady = true;
				
			}
		}
	});
}

function chathistoryHtml(thischatStorage){

	chatapp.$data.chatlog = [];
	
	if(thischatStorage.history && thischatStorage.history.length>0){
		chatpage = chatpage + 1;
		chatapp.$data.morelog = false;
		var chatlog = thischatStorage.history;
		var list = [];
		if (chatlog.length > 0) {
			pastSend = chatlog[0].sendTime;
			for (var i in chatlog) {
				chat = renderPast(chatlog[i],i);
				// 最后一条时，如还有历史消息，增加更多消息
				if(chat){
					list.push(chat);
				}
				// 最后一条时，如还有历史消息，增加更多消息
				if (i == chatlog.length - 1) {
					chatapp.$data.morelog = true;
					lastid = chatlog[i]['id'];
				}else{
					chatapp.$data.morelog = false;
				}
			}
		}
		list.reverse();
		chatapp.$data.chatlog = list.concat(chatapp.$data.chatlog);

		if (chatpage == 2) {

			chatapp.$nextTick(function(){
				// 渲染后，滚动到上一页的距离
				var scroll = document.getElementById('chat_content').scrollHeight;
				chatScrollTo(scroll);
				// 图片类型加载较慢，滚动到底部需要时间
				var timer_num = 0;
				var timer = setInterval(function() {
					timer_num++;
					var imgscroll = document.getElementById('chat_content').scrollHeight;
					chatScrollTo(imgscroll);
					if (timer_num > 20) {
						clearInterval(timer);
						timer = null;
					}
				}, 50);
				setTimeout(function(){
					picviewjs();
				},200);
			})
		}
		picviewjs();
		moreReady = true;
	}
	prepare();
}
// 渲染历史消息
function renderPast(msg,key) {
	var content = msg.content;
	
	var chat = {
			chatid: msg.chatid,
			mine: msg.mine,
			msgtype: msg.msgtype,
			avatar: msg.avatar,
			content: content,
			sendTime: msg.sendTime,
			fusertype: msg.fusertype,
			read: msg.read
		};

	if (msg.msgtype == 'ask'){
		if(content.indexOf('ask') != -1){
			var askid = content.replace('ask=','');
			chat.asktype = askid;
			chat.asktext = getAskContent(chat);
			chat.askstatus = msg.askstatus;
		}
	}else if (msg.msgtype == 'confirm'){
		if(content.indexOf('confirm') != -1){
			var confirmid = content.replace('confirm=','');
			chat.confirmtype = confirmid;
			chat.askvalue = msg.confirmcon;
			if(confirmid=='wx'){
				chat.askmsg   = chatapp.$data.linkman+'的微信号:';
			}else if(confirmid=='tel'){
				chat.askmsg   = chatapp.$data.linkman+'的手机号:';
			}
		}
	}else if(msg.msgtype == 'refuse'){
		if(content.indexOf('refuse') != -1){
			var refuseid = content.replace('refuse=','');
			chat.refusetype = refuseid;
			chat.refusetext = getRefuseContent(chat);
		}
	}else if (msg.msgtype == 'job'){
		if(content.indexOf('jobid') != -1){
			jid = content.replace('jobid=','');
			if(chatapp.$data.joblist[jid]){
				chat.job = chatapp.$data.joblist[jid];
				chat.jobid = jid;
				chat.ctime = timeFormat(parseInt(chat.sendTime));
				chat.newjob = msg.newjob;
				var gtuser = '';
				if(chat.newjob==1){
					gtuser = '你';
				}else{
					if (msg.fusertype == 1) {
						if (mine.usertype == 1) {
							gtuser = '你';
						} else {
							gtuser = '求职者';
						}
					} else {
						if (mine.usertype == 1) {
							gtuser = '招聘者';
						} else {
							gtuser = '你';
						}
					}
				}
				chat.newjob_tip = ' 由' + gtuser + '发起的沟通';
				showChatJob(jid);
				
				chatapp.$data.jobShowed = false;
			}
		}
		
	}else if (msg.msgtype == 'adminjob'){
		if(content.indexOf('jobid') != -1){
			jid = content.replace('jobid=','');
			if(chatapp.$data.adminjob[jid]){
				chat.job = chatapp.$data.adminjob[jid];
				chat.jobid = jid;
				chat.ctime = timeFormat(parseInt(chat.sendTime));
				chat.newjob = msg.newjob;
				var gtuser = '';
				if(chat.newjob==1){
					gtuser = '你';
				}else{
					if (msg.fusertype == 1) {
						if (mine.usertype == 1) {
							gtuser = '你';
						} else {
							gtuser = '招聘者';
						}
					} else {
						if (mine.usertype == 1) {
							gtuser = '求职者';
						} else {
							gtuser = '你';
						}
					}
				}
				chat.newjob_tip = ' 由' + gtuser + '发起的沟通';
				showChatJob(jid);
				chatapp.$data.jobShowed = false;
			}
		}
	}else if (msg.msgtype == 'resume'){
		if(content.indexOf('eid') != -1){
			chat.eid = content.replace('eid=','');
		}
	}else if (msg.msgtype == 'invite'){
		if(content.indexOf('inviteid') != -1){

			chat.msgtype = 'char';

			var qyid = content.replace('inviteid=','');

			chat.inviteid = qyid;

			if (inviteid != '' && inviteid == qyid) {
				inviteid = '';
			}
		}
	}else if (msg.msgtype == 'voice'){
		if(content.indexOf('voice[') != -1){
			chat.voice = content.replace(/(^voice\[)|(\]$)/g, '');
		}
		chat.voicelength = msg.voicelength;
		chat.voicestatus = msg.voicestatus;
		
	}else if (msg.msgtype == 'spview'){
		
	}else if (msg.msgtype == 'read'){
		return
	}else if (msg.msgtype == 'map') {
		let map = content.split('|');
		if(map.length == 4){
			chat.name = map[0];
			chat.url = map[1];
			chat.lat = map[2];
			chat.lng = map[3];
		}

	}else if (msg.msgtype == 'recall'){
		
	}else{
		if(content.indexOf('img[') != -1){
		 	chat.image = content.replace(/(^img\[)|(\]$)/g, '');
		}else{
			chat.content = rexContent(content);
		}
	}
	var time = parseInt(chat.sendTime);
	if (key == 0){
		chat.time = timeFormat(time);
	}
	if (pastSend ==0 || (pastSend - time > 1800 * 1000)) {
			
		chat.time = timeFormat(time);
		pastSend = time;
	}
	return chat;
	

}
//预览图片js点击监听初始化
function picviewjs(){
	$(".chat_photos").unbind("click");
	$('.chat_photos').click(function(event){
		event.stopPropagation();
        event.preventDefault();
		var url =	$(this).attr('src');
        var picjson={
		  "data": []
		}
        if(url){
            picjson.data.push({"src": url,"thumb": url});
        }
        
		layer.photos({
			photos: picjson
			,anim: 5 
		});
	})
}
function getAskContent(chat){
	var asktext = '';
	if(chat.asktype=='tel'){
		if(chat.mine){
			asktext = '正在请求互换电话，等待对方同意';
		}else{
			asktext = '我想要和您互换电话,您是否同意';
		}
	}else if(chat.asktype=='wx'){
		if(chat.mine){
			asktext = '正在请求互换微信，等待对方同意';
		}else{
			asktext = '我想要和您互换微信,您是否同意';
		}
	}
	return asktext;
}
function getRefuseContent(chat){
	var refusetext = '';
	if(chat.refusetype=='tel'){
		if(chat.mine){
			refusetext = '您已拒绝对方的互换电话请求';
		}else{
			refusetext = '对方已拒绝了您的互换电话请求';
		}
	}else if(chat.refusetype=='wx'){
		if(chat.mine){
			refusetext = '您已拒绝对方的互换微信请求';
		}else{
			refusetext = '对方已拒绝了您的互换微信请求';
		}
	}
	return refusetext;
}
function showChatJob(id) {
	
	chatapp.$data.chatjob = chatapp.$data.joblist[id];
}
// 对方未在线，发送提醒
function unSend(msg) {
	$.post(weburl + "/index.php?m=chat&c=unSend", {
		'nowtype': msg.ftype,
		'toid': msg.toid,
		'totype': msg.totype,
		'chatid':chatapp.$data.newchatid
	}, function(res) {

		if(res.error==1){

			var chatStorage = deepClone(chatapp.$data.chatStorage);
	
			var setArr = ['char', 'job', 'resume', 'invite', 'ask', 'confirm', 'refuse', 'spview','voice'];
			
			var msg = {
				'id':toid,
				'ftype':totype,
				'avatar':res.avatar,
				'linkman':chatapp.$data.linkman,
				'content':res.content,
				'msgtype':res.msgtype,
				'username':res.username,
				'timestamp':res.timestamp,
				'totype':mine.usertype,
				'timefromat':res.timefromat,
				'chatid':res.chatid,
			};

			if(setArr.indexOf(msg.msgtype) != -1 && typeof chatStorage[msg.id]!='undefined' && chatStorage[msg.id]){
				chatStorage[msg.id].history.unshift(msg);
				chatapp.$data.chatStorage = chatStorage;
			}

			var chatlist = deepClone(chatapp.$data.chatlist);

			if (toid != '' && msg.id == toid) {

				for(let i in chatlist){
					if(chatlist[i].id == msg.id){
	 					chatlist[i].timestamp = msg.timestamp;
	 					chatlist[i].time = timeFormat(msg.timestamp);
					}
				}

				chatapp.$data.chatlist = sortByField(chatlist,'timestamp','desc');

				chatRender(msg, 'get');
				
				setChatDesc(msg);
			}
		}

		
	},'json');
}
/*
chatStorage数据结构
[
	20034:{
		toid:20034,
		totype:1,
		
		cantel:1,
		canwx:1,
		linkman:'',
		identity:'',
		
		expect:{},
		joblist:[{...}],
		history:[{...}]
	}
]
**/
function getChatLocalStorage(toid){
	var csto = deepClone(chatapp.$data.chatStorage);
	
	if(toid){
		return (typeof csto[toid]!='undefined'&&csto[toid]) ? csto[toid]:null;
	}else{
		return csto;
	}

}

//切换后页面参数初始化
function initData(thischatStorage){
	
	jid = '';
	eid = '';
	totype = thischatStorage.totype;
	chatapp.$data.totype = totype;
	if(totype == 9){
		// 管理员
		toid = 'a' + thischatStorage.toid;
	}else{
		toid = thischatStorage.toid;
	}
	chatapp.$data.toid = toid;

	inviteid = '';
	
	chatapp.$data.towx = "";
	chatapp.$data.totel = "";
	chatapp.$data.cantel = thischatStorage.cantel;
	chatapp.$data.canwx = thischatStorage.canwx;
	chatapp.$data.inviteNum = thischatStorage.inviteNum;
	chatapp.$data.linkman = typeof thischatStorage.linkman!='undefined'?thischatStorage.linkman:'';
	chatapp.$data.stime = thischatStorage.stime_n;
	chatapp.$data.identity = typeof thischatStorage.identity!='undefined'?thischatStorage.identity:'';
	
	if(mine.usertype=='2'){
		var company = chatapp.$data.company;
		
		chatapp.$data.lastjob={
			job_x:company.x,
			job_y:company.y,
			job_mappic:company.mappic,
			job_address:company.address
		};
	}
	
	pastSend = 0;
	chatapp.$data.jobShowed = false;
	chatapp.$data.playMsgid = null;

	lastid = '';
	chatright = 0;
	sendTime = 0;
	chatpage = 1;
	comalljobnum  = 0;

	
	commentState = null;
	spconfirm = null;
	spint = null;
	zphnetSocketOnline = [];
	spviewBell = null;
	
	

	if(mine.usertype=='1'){

		if(totype=='9'){
			chatapp.$data.adminjob = thischatStorage.joblist?thischatStorage.joblist:{};
			singleHtml();
			chathistoryHtml(thischatStorage);
			setRead();
		}else{

			chatright = thischatStorage.chatright?thischatStorage.chatright:0;
			
			joblist = thischatStorage.joblist?thischatStorage.joblist:[];

			chatapp.$data.joblist = joblist;

			comalljobnum  = thischatStorage.comalljobnum?thischatStorage.comalljobnum:0;
			//本地职位数量小于对方表里职位数量的，且本地职位数量小于50条的，请求出对方所有职位，并设置本地缓存
			var jobnum = Object.keys(joblist);

			if(jobnum.length<comalljobnum && jobnum.length<50){

				$.post(weburl + "/index.php?m=chat&c=getAjaxJobs", {
					toid: toid,
					totype:totype
				}, function(res) {
					
					if(res.joblist){
						chatapp.$data.joblist = res.joblist;
						chatapp.$data.chatStorage[toid].joblist = joblist;
						singleHtml();
						chathistoryHtml(thischatStorage);
						setRead();
					}

				}, 'json');
			}else{
				singleHtml();
				chathistoryHtml(thischatStorage);
				setRead();
			}
		}
		
	}else{
		

		chatapp.$data.expect = thischatStorage.expect?thischatStorage.expect:null;

		singleHtml();
		chathistoryHtml(thischatStorage);
		setRead();
	}

	chatapp.$data.headshow = true;
	
}
function getAllChatOnlineData(toid_v){

	chatapp.$data.chatlist_load = true;

	$.post(weburl + "/index.php?m=chat&c=getAllChatOnlineData", {
		rand: Math.random()
	}, function(res) {
		
		if(mine.usertype=='1'){
			chatapp.$data.expect = res.mine.expect?res.mine.expect:null;
		}else{
			chatapp.$data.joblist = res.mine.joblist?res.mine.joblist:null;
			chatapp.$data.company = res.company?res.company:null;
		}
		
		chatapp.$data.chatStorage = res.toall;
		
		chatapp.$data.chatlist = res.rows;
		chatapp.$data.lastjob_arr = res.lastjob_arr;
		chatapp.$data.chatlist_load = false;
		
		chatapp.$nextTick(function(){
			if ($('#chatList').length > 0) {
				boxElement = document.getElementById("chatList");

				boxElement.addEventListener('scroll', listScroll);

				// 获取滚动条滚动区域盒子的高度
				chatlistch = boxElement.clientHeight;
			}
		})
		
		// 查询准备数据
		if (typeof toid_v !== 'undefined' && toid_v != '') {

			chatapp.toChat(toid_v, totype, mine.usertype);
		}

	}, 'json');
}
function setRead(msg){
	var setArr = ['char', 'job', 'resume', 'invite', 'ask', 'confirm', 'refuse', 'spview','adminjob', 'voice'];

	if (toid != '') {
		if(typeof msg!='undefined' && setArr.indexOf(msg.msgtype) == -1){
			return false;
		}
		if(totype=='9'){
			//toid=toid.replace('a','');
		}
		
		if(unreadchat.indexOf(parseInt(toid))!=-1){
			unreadchat.splice(unreadchat.indexOf(parseInt(toid)),1);
			
			if(!document[hiddenProperty]){
				
				window.localStorage.setItem("socketNewchat",JSON.stringify(unreadchat));
			}
		}
		// 收到消息处理
		setTimeout(function() {
			$.post(weburl + "/index.php?m=chat&c=setMsgStatus", {
				'id': toid,
				'type': totype,
				'nowid': mine.id,
				'nowtype': mine.usertype
			}, function(res) {
				if (typeof res.errmsg != 'undefined') {
					layer.msg(res.errmsg, 2, 8, function() {
						location.href = weburl;
						return;
					});
				}else if(typeof msg!='undefined'){
					if(msg.chatid){
						sendMessage('', 'read', msg.chatid);
					}
				}else{
					sendMessage('', 'read', 'all');
				}
			},'json');
		}, 500);
	}
}
// 展示聊天详情
function showSingle(id, type){
	
	layer.load();
	$.post("index.php?c=chat&act=single", {
		id: id,
		type: type
	}, function(res) {
		layer.closeAll('loading');
		var receive = res.receive;
		chatapp.$data.receive = receive;
		toid = receive.id;
		totype = receive.usertype;
		chatapp.$data.toid = receive.id;
		chatapp.$data.totype = receive.usertype;
		chatapp.$data.linkman = receive.linkman;
		chatapp.$data.stime = receive.stime_n;
		chatapp.$data.identity = receive.identity;
		chatapp.$data.cantel = res.cantel;
		chatapp.$data.canwx = res.canwx;
		chatapp.$data.headshow = true;
		singleHtml();

		getPrepare();
		lastid = '';
		sendTime = 0;
		pastSend = 0;
		chatpage = 1;
		chatapp.$data.jobShowed = false;
		chatapp.$data.expect = null;
		joblist = null;
		chatapp.$data.playMsgid = null;
		
	}, 'json');
}

function singleHtml(){

	//$("#unread_" + toid).text('');
	var chatlist = deepClone(chatapp.$data.chatlist);
	for(let i in chatlist){
		if(chatlist[i].id==toid){
			var listUnread = chatapp.$data.chatlist[i].unread;
			chatapp.$data.chatlist[i].unread = 0;
			var leftUnreadChatNum = $('#leftChatNum').html();
			if (leftUnreadChatNum == ''){
				leftUnreadChatNum = 0;
			}else{
				leftUnreadChatNum = parseInt(leftUnreadChatNum) - parseInt(listUnread);
			}
			if (leftUnreadChatNum > 0){
				$('#leftChatNum').html(leftUnreadChatNum);
			}else{
				$('#leftChatNum').html('');
			}
		}
	}

	$("#cbr_y").show();
	$("#cbr_n").hide();
	
	jsPrepare();
}

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
//聊天对象记录翻页
function listScroll() {
	
	if (boxElement.scrollHeight - boxElement.scrollTop <= (chatlistch + 15)) {
		
		if (mhdata) {
			mhpage = mhpage + 1;
			getmh(mhpage);
		}
	}
}

function getmh(page) {

	mhdata = false;
	var loading = layer.load('执行中，请稍候...', 0);
	$.post(weburl + '/index.php?m=chat&c=getmh', {
		page: page
	}, function(res) {
		layer.closeAll();
		if (res.error == 0) {
			mhdata = true;

			var cstorage = res.chatstorage;
			for(var i in cstorage){
				chatapp.$data.chatStorage[i] = cstorage[i];
			}

			var his = res.history;
			
			var chatlist = deepClone(chatapp.$data.chatlist);
			var chatlist_idarr = [];

			for(let i in chatlist){
				chatlist_idarr.push(chatlist[i].id);
			}

			if(his && his.length>0){

				for (var i in his) {

					chatapp.$data.chatlist.push(his[i]);
					
				}
			}
			
		}
	},'json');
}


//表情库
function faces(fc) {
	var alt = {
			1: '[龇牙]',2: '[调皮]',3: '[流汗]',4: '[偷笑]',5: '[再见]',6: '[敲打]',7: '[擦汗]',8: '[流泪]',9: '[大哭]',10: '[嘘]',11: '[酷]',12: '[抓狂]',13: '[可爱]',14: '[色]',15: '[害羞]',16: '[得意]',17: '[吐]',18: '[微笑]',19: '[怒]',20: '[尴尬]',21: '[惊恐]',22: '[冷汗]',23: '[白眼]',24: '[傲慢]',25: '[难过]',26: '[惊讶]',27: '[疑问]',28: '[么么哒]',29: '[困]',30: '[憨笑]',31: '[撇嘴]',32: '[阴险]',33: '[奋斗]',34: '[发呆]',35: '[左哼哼]',36: '[右哼哼]',74: '[抱抱]',37: '[坏笑]',38: '[鄙视]',39: '[晕]',40: '[可怜]',41: '[饥饿]',42: '[咒骂]',43: '[折磨]',44: '[抠鼻]',45: '[鼓掌]',46: '[糗大了]',47: '[打哈欠]',48: '[快哭了]',49: '[吓]',50: '[闭嘴]',51: '[大兵]',52: '[委屈]',53: '[NO]',54: '[OK]',56: '[弱]',57: '[强]',60: '[握手]',63: '[胜利]',58: '[抱拳]',66: '[凋谢]',99: '[米饭]',108: '[蛋糕]',112: '[西瓜]',70: '[啤酒]',89: '[瓢虫]',62: '[勾引]',82: '[爱你]',69: '[咖啡]',72: '[月亮]',68: '[刀]',55: '[差劲]',59: '[拳头]',65: '[便便]',79: '[炸弹]',107: '[菜刀]',82: '[心碎了]',83: '[爱心]',71: '[太阳]',97: '[礼物]',92: '[皮球]',137: '[骷髅]',123: '[闪电]',80: '[猪头]',67: '[玫瑰]',98: '[篮球]',64: '[乒乓]',101: '[红双喜]',139: '[麻将]',73: '[彩带]',61: '[爱你]',95: '[示爱]',111: '[衰]',109: '[蜡烛]'
		},
		arr = {};
	$.each(alt, function(index, item) {
		arr[item] = weburl + '/js/im/emoji_' + index + '@2x.png';
	});
	if (fc) {
		return arr[fc];
	} else {
		return arr;
	}
}

function face() {
	if ($('#face').hasClass('sendbq_i')) {
		$('#commonly').removeClass('none');
		// $('#face').attr('class','chat_footer_keyboard');
		// $('#face').addClass('chat_footer_keyboard');
		$('#face').removeClass("sendbq_i");
		var content = '',
			faceArr = faces();

		$.each(faceArr, function(key, item) {
			content += '<li title="' + key + '"><img src="' + item + '"></li>';
		});
		content = '<ul id="face_content" class="chat_face">' + content + '</ul>';
		$('#commonly').html(content);

		$("#face_content").off("click");
		$('#face_content').find('li').on('click', function(elem) {
			var title = elem.currentTarget.title;
			focusInsert(document.getElementById("send_content"), 'face' + title + ' ', true);
		});
	} else {
		$('#commonly').addClass('none');
		// $('#face').attr('class','sendbq');
		$('#face').addClass("sendbq_i");
		document.getElementById("send_content").focus();
	}
}
//在焦点处插入内容
function focusInsert(obj, str, nofocus) {

	var result, val = obj.value;
	nofocus || obj.focus();
	if (document.selection) { //ie
		result = document.selection.createRange();
		document.selection.empty();
		result.text = str;
	} else {
		result = [val.substring(0, obj.selectionStart), str, val.substr(obj.selectionEnd)];
		nofocus || obj.focus();
		obj.value = result.join('');
	}
}

function commonHide() {
	$('#commonly').addClass('none');
	$('#face').attr('class', 'sendbq');
	$('#face').addClass("sendbq_i");
	
}
// 收到视频面试通知处理
function comment(data) {
	if (!commentState) {
		if (data.to == mine.id) {
			var timestamp = new Date().getTime();
			commentState = 'receive';
			window.localStorage.setItem("isspview", "0");
			if(!spviewBell && !document[hiddenProperty]){
				spviewBell = document.createElement("audio");
				spviewBell.src = weburl + '/js/trtc/video.mp3';
				spviewBell.loop = true;
				spviewBell.play();
			}
			spconfirm = layer.confirm('收到视频面试邀请，是否接受', {
					btn: ['接受', '拒绝']
				},
				function() {
					layer.close(spconfirm);
					if(spint){
						clearInterval(spint);
						spint = null;
					}
					commentState = null;
					window.localStorage.setItem("isspview", "1");
					if(data.sid){
						// 企业发布的视频面试
						window.location.href = weburl + '/member/?c=spview&act=sproom&id=' + data.sid;
					}else if (data.zid){
						// 网络招聘会
						window.location.href = weburl + '/member/?c=spview&act=webrtc&zid=' + data.zid +'&fuid=' + data.id;
					}else{
						// 普通单对单
						window.location.href = weburl + '/member/?c=spview&act=webrtc&fuid=' + data.id;
					}
				},
				function() {
					layer.close(spconfirm);
					if(spint){
						clearInterval(spint);
						spint = null;
					}
					var msg = {
						type: 'commentRefused',
						data: {
							mine: {
								id: data.to,
								timestamp: timestamp
							},
							to: {
								id: data.id
							}
						}
					};
					socket.send(JSON.stringify(msg));
					commentState = null;
					window.localStorage.setItem("isspview", "2");
					// 拒绝视频，记录聊天记录
					var pdata = {
						to: data.id,
						totype: mine.usertype == '2' ? 1 : 2,
						content: 'refused',
						timestamp: timestamp
					};
					
					$.post(weburl + "/index.php?m=chat&c=spviewChat", pdata, function(data) {});
				});
			if(spWait == ''){
				spWait = 60;
			}else{
				spWait = parseInt(spWait);
			}
			spint = setInterval(function(){
				layer.close(spconfirm);
				if(spviewBell){
					spviewBell.pause();
					spviewBell = null;
				}
			},spWait*1000);
			
			newChatMsg = true;
			if (document[hiddenProperty]) {
				var titAn = true;
				if (titledsq) {
					clearInterval(titledsq);
				}
				titledsq = setInterval(function() {
					if (titAn) {
						document.title = '【新消息】';
						titAn = false;
					} else {
						document.title = $('#zwf').text();
						titAn = true;
					}
				}, 1000);
			}
		}
	} else {
		if (commentState != 'receive') {
			var msg = {
				type: 'commenting',
				data: {
					mine: {
						id: data.to,
						timestamp: new Date().getTime()
					},
					to: {
						id: data.id
					}
				}
			};
			socket.send(JSON.stringify(msg));
		}
	}
}
// 对方取消视频请求
function closesp(msg) {
	if (msg.to == mine.id) {
		layer.closeAll();
		layer.msg('对方取消视频请求', 2, 8);
		commentState = null;
		if(spviewBell){
			spviewBell.pause();
			spviewBell = null;
		}
		if(spint){
			clearInterval(spint);
			spint = null;
		}
	}
}
// 告知自己的全部连接，关闭是否面试的询问框
function closeSpConfirm(msg) {
	if (msg.to == mine.id) {
		layer.close(spconfirm);
		commentState = null;
		if(spint){
			clearInterval(spint);
			spint = null;
		}
	}
}


function savehh() {

	var hhtype = $('#hhtype').val();
	var hhvalue = '';

	if (hhtype == 'wx') {

		hhvalue = $('#wxinput').val();

	} else if (hhtype == 'tel') {

		hhvalue = $('#telinput').val();

		if (hhvalue == '') {

			layer.msg('请填写手机号', 2, 8);
			return false;

		} else if (!isjsMobile(hhvalue)) {

			layer.msg('手机格式错误！', 2, 8);
			return false;
		}
	}

	if (hhvalue) {

		var sendtype = $('#sendtype').val();

		if (sendtype == 'sendask') { //发送请求互换

			postAsk(hhtype, hhvalue);

		} else if (sendtype == 'sendconfirm') { //发送确认互换

			postConfirm(hhtype, hhvalue);

		}
	} else {
		if(hhtype == 'wx'){
			layer.msg('请填写微信号', 2, 8);
		}else if(hhtype == 'tel'){
			layer.msg('请填写联系电话', 2, 8);
		}

		
	}
}
//发送请求互换
function postAsk(ask, askvalue) {

	var asktext = '';

	if (ask && askvalue) {

		$.post(weburl + "/index.php?m=chat&c=checkCanAsk", {
			toid: toid,
			totype: totype,
			ask: ask,
			askvalue: askvalue
		}, function(data) {

			var data = eval('(' + data + ')');

			if (data.error == '9') {

				$('#hhshow').hide();

				if (ask == 'tel') {
					asktext = '我想要和您互换电话，您是否同意';
					chatapp.$data.cantel = 3;
				} else if (ask == 'wx') {
					asktext = '我想要和您互换微信，您是否同意';
					chatapp.$data.canwx = 3;
				}
				sendMessage(1, 'ask', ask);

			} else {

				layer.msg(data.msg, 2, 8);

				if (data.error == '7') {

					huhuanStatus(ask, '3');
				}
			}
		});
	}
}
//发送确认互换
function postConfirm(ask, askvalue) {

	if (ask && askvalue) {

		var chatid = $('#chatlogid').val();

		$.post(weburl + "/index.php?m=chat&c=confirmAsk", {
			toid: toid,
			totype: totype,
			ask: ask,
			askvalue: askvalue,
			chatid: chatid
		}, function(data) {

			var data = eval('(' + data + ')');

			if (data.error == '1') {

				$('#hhshow').hide();

				if (data.towx) {
					chatapp.$data.towx = data.towx;
					chatapp.$data.canwx = 1;
				}
				if (data.totel) {
					chatapp.$data.totel = data.totel;
					chatapp.$data.cantel = 1;
				}

				sendMessage(askvalue, 'confirm', ask);

				banClickHtml(chatid, 'confirm');
			} else {
				layer.msg('参数错误，请重试', 2, 8);
			}
		});
	}
}

function banClickHtml(chatid, type) {
	var chatlog = deepClone(chatapp.$data.chatlog);
	for(let i in chatlog){
		if(chatlog[i].chatid == chatid){
			if(type == 'confirm'){
				chatlog[i].askstatus = 1;
			}else{
				chatlog[i].askstatus = 2;
			}
			break;
		}
	}
	chatapp.$data.chatlog = chatlog;
	
	var chatStorage = deepClone(chatapp.$data.chatStorage);
	
	if(typeof chatStorage[toid]!='undefined' && chatStorage[toid]){
		var chathis = chatStorage[toid].history;
		for(let j in chathis){
			if(chathis[j].chatid == chatid){
				if(type == 'confirm'){
					chathis[j].askstatus = 1;
				}else{
					chathis[j].askstatus = 2;
				}
				break;
			}
		}
		chatStorage[toid].history = chathis;
	
		chatapp.$data.chatStorage = chatStorage;
	}
}

function huhuanStatus(type, val) {
	if (type == 'wx') {
		chatapp.$data.canwx = val;
		
	} else if (type == 'tel') {
		chatapp.$data.cantel = val;
		
	}
}



// 查询网络招聘会在线用户
function sendGetonline(){
	if(socket && socket.readyState == 1){
		if(lineList.length > 0){
			var msg = {
				type: 'getListOnline',
				data: {
					mine: {
						id: mine.id,
						list: lineList,
						timestamp: new Date().getTime()
					}
				}
			};
			socket.send(JSON.stringify(msg));
			zphnetSocketOnline = [];
			setTimeout(function(){
				if(zphnetSocketOnline.length == 0){
					$(".zphnetOnline").hide();
				}
			},1500);
		}
	}
}
// 网络招聘会在线用户查询结果处理
function onGetListOnline(data){
	if(data.id == mine.id){
		if(data.list.length > 0){
			zphnetSocketOnline = data.list;
			for (var i in data.list){
				if($("#dxzph_" + data.list[i]).length > 0){
					$("#dxzph_" + data.list[i]).show();
				}
			}
		}
	}
}
// 接收管理员发送的职位
function getAdminjob(msg){
	var jobid = msg.content.replace('jobid=', '');
	$.post(weburl + "/index.php?m=chat&c=getJob",{jobid: jobid}, function(res){
		if(res.id){
			

			var	chat = {
				    chatid: new Date().getTime(),
					mine: false,
					msgtype: msg.msgtype,
					avatar: msg.avatar,
					content: msg.content
				};

			chat.job = res;
			chat.ctime = timeFormat(msg.timestamp);
			// 处理时间
			if (msg.timestamp - sendTime > 60 * 1000) {
				chat.time = timeFormat(msg.timestamp);
				sendTime = msg.timestamp;
			}
			
			if(msg.chatid){
				chat.chatid = msg.chatid;
			}
			chatapp.$data.chatlog.push(chat);

			chatapp.$nextTick(function(){
				var scroll = document.getElementById('chat_content').scrollHeight;
				chatScrollTo(scroll);
			})
			setRead(msg);
		}
	},'json');
}
function goBack(){
    if (navigator.userAgent.indexOf('Firefox') >= 0 ||
		navigator.userAgent.indexOf('Opera') >= 0 ||
		navigator.userAgent.indexOf('Safari') >= 0 ||
		navigator.userAgent.indexOf('Chrome') >= 0 ||
		navigator.userAgent.indexOf('WebKit') >= 0){

		if(window.history.length > 1){
			window.history.go( -1 );
		}else{
			window.location.reload();
		}
	}else{ //未知的浏览器
		window.history.go( -1 );
	}
}

// 收到消息撤回实际处理
function recallChat(uid,chatid, isMine){

	var chatStorage = deepClone(chatapp.$data.chatStorage);
	var chatlog = deepClone(chatapp.$data.chatlog);

	var content = isMine ? '你撤回了一条消息' : '对方撤回了一条消息';
	if(typeof chatStorage[uid]!='undefined' && chatStorage[uid]){
		
		for(let i in chatlog){
			if(chatlog[i].chatid == chatid){
				chatlog[i].msgtype = 'recall';
				chatlog[i].content =  content;
				break;
			}
		}
		chatapp.$data.chatlog = chatlog;

		var chathis = chatStorage[uid].history;
		for(let i in chathis){
			if(chathis[i].chatid == chatid){
				chathis[i].msgtype = 'recall';
				chathis[i].content =  content;
				break;
			}
		}
		chatStorage[uid].history = chathis;

		chatapp.$data.chatStorage = chatStorage;
	}
}

function greetingSwitch(){

	var cmsg = '打开后，新的聊天将发送问候语，是否确认？';
	var greetingtext = '开启';
	var greeting_switch = chatapp.$data.greeting_switch;
	if(greeting_switch=='1'){
		cmsg = '关闭后，新的聊天将不会发送问候语，是否确认？';
	}

	var greetingconfirm = layer.confirm(cmsg, {btn: ['确认', '算了']},
		function() {
			layer.close(greetingconfirm);
			$.post(weburl + "/index.php?m=chat&c=greetingSwitch",{greeting_switch: greeting_switch}, function(res){
				if (res.errcode == 9) {
					layer.msg(res.msg, 2, 9,function(){
						
						chatapp.$data.greeting_switch = res.switch;
					});
				} else {
					layer.msg(res.msg, 2, 8);
				}
			},'json');
		},
		function() {
			layer.close(greetingconfirm);
		}
	);
	
}
