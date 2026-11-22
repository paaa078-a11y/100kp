
var countNum = 0,
	scrollHeight = 0,
	socket = null,
	islink = 1,
	ping_timer = null;
//浏览器内部通过storage通讯
window.addEventListener("storage", function(e) {
	// 其他页面退出登录本页面需要关闭socket
	if (e.key == 'socketState' && e.newValue == '2') {
		if(socket && socket.readyState == 1){
			socket.close();
		}
	}
});
$(function() {
	
	nowCount();
	
	if ("WebSocket" in window) {
		webSocket(socketUrl);
		chatOnLoad();
		hiddenCommon();
	} else {
		console.log("您的浏览器不支持聊天!");
	}
	// 发送消息
	$('#send').click(function() {
		if(socket && socket.readyState == 1){
			send();
		}else{
			webSocket(socketUrl);
			var timer = setInterval(function(){
				if(socket && socket.readyState == 1){
					clearInterval(timer);
					send();
				}
			},500);
			setTimeout(function(){
				if(timer){
					clearInterval(timer);
				}
			},10000);
		}
	});
	$("body").click(function(e){
		var con = $("#commonly");   // 设置目标区域
		var face = $("#face");
		if ((!con.is(e.target) && con.has(e.target).length === 0) && (!face.is(e.target) && face.has(e.target).length === 0)) {
			commonHide();
		}
	});
});
function webSocket(socketUrl){
	if(islink == 1){
		if (ping_timer) {
			clearInterval(ping_timer);
			ping_timer = null;
		}
		socket = new WebSocket(socketUrl);
		socket.onopen = function() {
			islink = 1;
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
			islink = 2;
			socket = null;
			if(ping_timer){
				clearInterval(ping_timer);
				ping_timer = null;
			}
		};

		socket.onclose = function() {
			socket = null;
			if(ping_timer){
				clearInterval(ping_timer);
				ping_timer = null;
			}
		};
		//捕捉socket端发来的事件
		socket.onmessage = function(event) {
			var e = JSON.parse(event.data);
			//console.log(e);
			switch (e.type) {
				//显示收到的消息
				case 'xjhMessage':
					getMessage(e.data);
					break;
				case 'error':
					islink = 2;
					if(ping_timer){
						clearInterval(ping_timer);
						ping_timer = null;
					}
					break;
				case 'startLive':
					startLive(e.data);
					break;
				case 'stopLive':
					stopLive(e.data);
					break;
				case 'xjhCount':
					xjhCount(e.data);
					break;
				case 'xjhChatting':
					xjhChatting(e.data);
					break;
				case 'xjhNowcom':
					xjhNowcom(e.data);
					break;
			}
		};
	}
}
// 加载聊天数据
function chatOnLoad(){
	$.post(wapurl + "/index.php?c=chat&a=getToken", {
		chattype: 'xjh'
	}, function(res) {
		if (res) {
			mine = res.mine;
			if (socket && socket.readyState == 1){
				bindUser(res);
			}else{
				var t = setInterval(function(){
					if (socket && socket.readyState == 1){
						clearInterval(t);
						t = null;
						bindUser(res);
					}
				},500);
				setTimeout(function(){
					if(t){
						clearInterval(t);
						t = null;
					}
				},10000);
			}
		}
	},'json');
}
function bindUser(res){
	if(res.yuntoken && res.yuntoken.token && typeof(xjhsyncid) != "undefined" && xjhsyncid != ""){
		// 已登录
		var yuntoken = res.yuntoken.token;
		var bind = {
			type: 'bind',
			data: {
				uid: mine.id,
				usertype: mine.usertype,
				yuntoken: yuntoken,
				group: 'xjh_' + xjhsyncid
			}
		}
		socket.send(JSON.stringify(bind));
		window.localStorage.setItem("socketState", "1");
	}else if(res.data && typeof(xjhsyncid) != "undefined" && xjhsyncid != ""){
		// 未登录
		var bindData = res.data;
		bindData.group = 'xjh_' + xjhsyncid;
		var bind = {
			type: 'bind',
			data: bindData
		}
		socket.send(JSON.stringify(bind));
		window.localStorage.setItem("socketState", "1");
	}else{
		socket = null;
		islink = 2;
		if(ping_timer){
			clearInterval(ping_timer);
			ping_timer = null;
		}
	}
}
function hiddenCommon(){
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
				webSocket(socketUrl);
				chatOnLoad();
			}
		}
	});
}
function back(){
	history.back();
}

// 发送消息
function send() {
	var content = document.getElementById('send_content');
	if (content.value != '') {
		// 发送聊天
		sendMessage(content.value,'char','');
		content.value = '';
	}
}
function getMessage(msg){
	if(msg.xjhid == 'xjh_' + xjhsyncid && (msg.id != mine.id || (msg.id == mine.id && msg.plat != 'wap'))) {
		// 渲染消息内容
		if(msg.msgtype == 'zan'){
			var nowzan = yunvue.$data.zan;
			var newzan = parseInt(msg.content) - parseInt(nowzan);
			if(newzan > 0){
				jsDianzan(newzan);
				yunvue.$data.zan = msg.content;
			}
		}else{
			var	newlog = {
			    chatid: msg.chatid,
				msgtype: msg.msgtype,
				avatar: msg.avatar,
				username: msg.username,
				usertype: msg.usertype,
				content: rexContent(msg.content)
			};
			if(msg.job){
				newlog.job = msg.job;
			}
			yunvue.$data.chatlist.push(newlog);
			setTimeout(function(){
				var scroll = document.getElementById('chat_content').scrollHeight;
				scrollTo(scroll);
			},100);
		}
	}
}
// 发送消息
function sendMessage(content, type){
	if(socket && socket.readyState == 1){
		// 根据消息类型区分保存内容
		var mineData = {
			id: mine.id,
			avatar: mine.avatar,
			username: mine.username,
			usertype: mine.usertype,
			linkman: mine.linkman,
			timestamp: new Date().getTime(),
			xjhid: 'xjh_' + xjhsyncid,
			xid: xjhid,
			msgtype: type,
			caster:0,
			plat: 'wap'
		};
		if(type != 'zan'){
			var checkdata = {
				id: mine.id,
				content: content,
				xjhid: xjhid,
				timestamp: mineData.timestamp,
			};
			$.post(wapurl+"index.php?c=chat&a=checkdata", checkdata, function (res) {
				if(res.errcode){
					showToast(res.msg)
				}else{
					if(res.msgcontent != ''){
						mineData.content = res.msgcontent;
						var message = {
							type: 'xjhMessage',
							data: {
								mine: mineData
							}
						};
						socket.send(JSON.stringify(message));
						// 渲染消息内容
						// 渲染消息内容
						mineData.content = rexContent(mineData.content);
						yunvue.$data.chatlist.push(mineData);
						setTimeout(function(){
							var scroll = document.getElementById('chat_content').scrollHeight;
							scrollTo(scroll);
						},100);
					}else{
						showToast('请慎重发言')
					}
				}
			},'json');
		}else{
			// 点赞数据
			mineData.content = content;
			var message = {
				type: 'xjhMessage',
				data: {
					mine: mineData
				}
			};
			socket.send(JSON.stringify(message));
		}
	}else{
		showToast(chat_name+'功能加载中')
	}
}
// 转义内容
function rexContent(content){
	content = (content||'').replace(/&(?!#?[a-zA-Z0-9]+;)/g, '&amp;')
    .replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/'/g, '&#39;').replace(/"/g, '&quot;') //XSS
	.replace(/face\[([^\s\[\]]+?)\]/g, function(face){  //转义表情
		var alt = face.replace(/^face/g, '');
		return '<img class="iconImg" alt="'+ alt +'" title="'+ alt +'" src="' + faces([alt]) + '">';
	})
	.replace(/img\[([^\s]+?)\]/g, function(img){  //转义图片
		return '<img class="chat_photos" src="' + img.replace(/(^img\[)|(\]$)/g, '') + '">';
    })
	return content;
}
// 页面滚动
function scrollTo(ypos) {
	var clientHeight = document.documentElement.clientHeight;
	if((ypos + 300) > clientHeight){
		// 兼容各种浏览器
		document.getElementById('chat_content').scrollTop  = ypos;
	}
}

function startLive(e){
	if(typeof(xjhsyncid) != "undefined" && xjhsyncid != ""){
		if(xjhsyncid == e.xjhid && livestatus != '2'){
			$.post(wapurl+"index.php?c=xjhlive&a=getXjhlive",{id: xjhid},function(res){
				if(res && res.hls){
					$("#live_hls").val(res.hls);
				}
				if (document.getElementById('live_video')) {
					if(player){
						player.dispose();
						player = null;
					}
					livePlay();
				}else{
					$('#waitshow').hide();
					$('.sp_img').hide();
					$('.number').show();
					$('#livediv').append('<div id="live_video" class="show_box_header_opacity"></div>');
					livePlay();
				}
			},'json');
		}
	}
}
function stopLive(e){
	if(typeof(xjhsyncid) != "undefined" && xjhsyncid != ""){
		if(xjhsyncid == e.xjhid){
			if(player){
				player.dispose();
				player = null;
			}
			setTimeout(function(){
				var html = '<div><p>直播已结束</p><span>谢谢您的观看</span></div>';
				$("#livebody").html(html);
			},2000);
		}
	}
}
function xjhChatting(e){
	if(typeof(xjhsyncid) != "undefined" && xjhsyncid != ""){
		if(xjhsyncid == e.xjhid){
			yunvue.$data.nochat = e.chatting;
		}
	}
}
function xjhNowcom(e){
	if(typeof(xjhsyncid) != "undefined" && xjhsyncid != ""){
		if(xjhsyncid == e.xjhid){
			yunvue.$data.comlist.forEach(function(item,index){
				if(item.id == e.uid){
					item.status = 1;
					yunvue.$data.explain = yunvue.$data.comlist[index];
					yunvue.$data.comdisplay = yunvue.$data.comlist[index];
				}else{
					item.status = 2;
				}
			});
			yunvue.$data.comlist.sort((a, b) => {
				return a.status > b.status ? 1 : -1;
			});
		}
	}
}
function inputBlur() {
	setTimeout(function(){
		window.scrollBy(0,5);
		window.scrollBy(0,-5);
	},200)
}
// 统计同时观看人数
function xjhCount(msg){
	if(livestatus != 1){
		countNum = msg.count;
	}
}
// 数据统计
function nowCount(){
	if(livestatus != 1){
		setInterval(function(){
			$.post(wapurl + 'index.php?c=xjhlive&a=getAjaxHits', {id: xjhid, num: countNum}, function(res){
				$("#looknum").text(res.hits);
			},'json');
		},5000);
	}
}
//表情库
function faces(fc){
    var alt = {1:'[龇牙]',2:'[调皮]',3:'[流汗]',4:'[偷笑]',5:'[再见]',6:'[敲打]',7:'[擦汗]',8:'[流泪]',9:'[大哭]',10:'[嘘]',11:'[酷]',12:'[抓狂]',13:'[可爱]',14:'[色]',15:'[害羞]',16:'[得意]',17:'[吐]',18:'[微笑]',19:'[怒]',20:'[尴尬]',21:'[惊恐]',22:'[冷汗]',23:'[白眼]',24:'[傲慢]',25:'[难过]',26:'[惊讶]',27:'[疑问]',28:'[么么哒]',29:'[困]',30:'[憨笑]',31:'[撇嘴]',32:'[阴险]',33:'[奋斗]',34:'[发呆]',35:'[左哼哼]',36:'[右哼哼]',74:'[抱抱]',37:'[坏笑]',38:'[鄙视]',39:'[晕]',40:'[可怜]',41:'[饥饿]',42:'[咒骂]',43:'[折磨]',44:'[抠鼻]',45:'[鼓掌]',46:'[糗大了]',47:'[打哈欠]',48:'[快哭了]',49:'[吓]',50:'[闭嘴]',51:'[大兵]',52:'[委屈]',53:'[NO]',54:'[OK]',56:'[弱]',57:'[强]',60:'[握手]',63:'[胜利]',58:'[抱拳]',66:'[凋谢]',99:'[米饭]',108:'[蛋糕]',112:'[西瓜]',70:'[啤酒]',89:'[瓢虫]',62:'[勾引]',82:'[爱你]',69:'[咖啡]',72:'[月亮]',68:'[刀]',55:'[差劲]',59:'[拳头]',65:'[便便]',79:'[炸弹]',107:'[菜刀]',82:'[心碎了]',83:'[爱心]',71:'[太阳]',97:'[礼物]',92:'[皮球]',137:'[骷髅]',123:'[闪电]',80:'[猪头]',67:'[玫瑰]',98:'[篮球]',64:'[乒乓]',101:'[红双喜]',139:'[麻将]',73:'[彩带]',61:'[爱你]',95:'[示爱]',111:'[衰]',109:'[蜡烛]'},arr = {};
	
	$.each(alt, function(index, item){
		arr[item] = weburl + '/js/im/emoji_'+ index +'@2x.png';
    });
	if(fc){
		return arr[fc];
	}else{
		return arr;
	}
}
function face(){
	if($('#face').hasClass('sendbq')){
		$('#commonly').removeClass('none');
		$('#face').attr('class','chat_footer_keyboard');
		var content = '',
			faceArr = faces();
			
		$.each(faceArr, function(key, item){
			content += '<li title="'+ key +'"><img src="'+ item +'"></li>';
		});
		content = '<ul id="face_content" class="chat_face">'+ content +'</ul>';
		$('#commonly').html(content);
		
		$("#face_content").off("click");
		$('#face_content').find('li').on('click', function(elem){
			var title = elem.currentTarget.title;
			focusInsert(document.getElementById("send_content"), 'face' + title + ' ', true);
		});
	}else{
		$('#commonly').addClass('none');
		$('#face').attr('class','sendbq');
		document.getElementById("send_content").focus();
	}
}
//在焦点处插入内容
function focusInsert(obj, str, nofocus){
    var result, val = obj.value;
    nofocus || obj.focus();
    if(document.selection){ //ie
		result = document.selection.createRange(); 
		document.selection.empty(); 
		result.text = str; 
    } else {
		result = [val.substring(0, obj.selectionStart), str, val.substr(obj.selectionEnd)];
		nofocus || obj.focus();
		obj.value = result.join('');
    }
}
function commonHide(){
	$('#commonly').addClass('none');
	$('#face').attr('class','sendbq');
}