let sy_weburl = localStorage.getItem("sy_weburl")
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
			if(carr[i].indexOf(sy_weburl + '/js/im/emoji_') > -1){
				nc.push({t:'emoji',n:carr[i]});
			}else{
				nc.push({t:'char',n:carr[i]});
			}
		}
	}
	return nc;
}
//表情库
function faces (fc){
	var alt = alt = {1:'[龇牙]',2:'[调皮]',3:'[流汗]',4:'[偷笑]',5:'[再见]',6:'[敲打]',7:'[擦汗]',8:'[流泪]',9:'[大哭]',10:'[嘘]',11:'[酷]',12:'[抓狂]',13:'[可爱]',14:'[色]',15:'[害羞]',16:'[得意]',17:'[吐]',18:'[微笑]',19:'[怒]',20:'[尴尬]',21:'[惊恐]',22:'[冷汗]',23:'[白眼]',24:'[傲慢]',25:'[难过]',26:'[惊讶]',27:'[疑问]',28:'[么么哒]',29:'[困]',30:'[憨笑]',31:'[撇嘴]',32:'[阴险]',33:'[奋斗]',34:'[发呆]',35:'[左哼哼]',36:'[右哼哼]',74:'[抱抱]',37:'[坏笑]',38:'[鄙视]',39:'[晕]',40:'[可怜]',41:'[饥饿]',42:'[咒骂]',43:'[折磨]',44:'[抠鼻]',45:'[鼓掌]',46:'[糗大了]',47:'[打哈欠]',48:'[快哭了]',49:'[吓]',50:'[闭嘴]',51:'[大兵]',52:'[委屈]',53:'[NO]',54:'[OK]',56:'[弱]',57:'[强]',60:'[握手]',63:'[胜利]',58:'[抱拳]',66:'[凋谢]',99:'[米饭]',108:'[蛋糕]',112:'[西瓜]',70:'[啤酒]',89:'[瓢虫]',62:'[勾引]',82:'[爱你]',69:'[咖啡]',72:'[月亮]',68:'[刀]',55:'[差劲]',59:'[拳头]',65:'[便便]',79:'[炸弹]',107:'[菜刀]',82:'[心碎了]',83:'[爱心]',71:'[太阳]',97:'[礼物]',92:'[皮球]',137:'[骷髅]',123:'[闪电]',80:'[猪头]',67:'[玫瑰]',98:'[篮球]',64:'[乒乓]',101:'[红双喜]',139:'[麻将]',73:'[彩带]',61:'[爱你]',95:'[示爱]',111:'[衰]',109:'[蜡烛]'},
		arr = {};
	
	for (let i in alt){
		arr[alt[i]] = sy_weburl + '/js/im/emoji_'+ i +'@2x.png';
	}
	if(fc){
		return arr[fc];
	}else{
		return arr;
	}
}

//滚动
function chatScrollTo(ypos) {
	var clientHeight = document.getElementById('chat_content').clientHeight;
	if(ypos > clientHeight){
		// 兼容各种浏览器
		document.getElementById('chat_content').scrollTop = ypos;
	}
}
// 格式化时间戳
function timeFormat(stime) {
	var nowtime = new Date(stime);
	var today = new Date(new Date().setHours(0, 0, 0, 0)).getTime();
	var year = nowtime.getFullYear(),
		month = nowtime.getMonth(),
		day = nowtime.getDate(),
		hour = nowtime.getHours(),
		minute = nowtime.getMinutes(),
		second = nowtime.getSeconds();
	if(stime < today){
		month = month + 1;
		if (month < 10) {
			month = '0' + month;
		}
		if (day < 10 && day > 0) {
			day = '0' + day;
		}
		return year + '-' + month + '-' + day;
	}else{
		if (hour < 10 && hour > 0) {
			hour = '0' + hour;
		}
		if (minute < 10 && minute > 0) {
			minute = '0' + minute;
		}
		if (second < 10 && second > 0) {
			second = '0' + second;
		}
		return hour + ':' + minute + ':' + second;
	}
}

function setChatDesc(msg){
	if (msg.ftype == '1' || msg.ftype == '2') {
		var content = msg.content;
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
		
		return content;
	}
}

function getScrollHeight() {
	return Math.max(document.body.scrollHeight, document.documentElement.scrollHeight);
}

// 渲染历史消息
function renderPast(msg,key,_this) {
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
				chat.askmsg   = _this.linkman+'的微信号:';
			}else if(confirmid=='tel'){
				chat.askmsg   = _this.linkman+'的手机号:';
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
			if(_this.joblist[jid]){
				chat.job = _this.joblist[jid];
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
			}
		}
		
	}else if (msg.msgtype == 'adminjob'){
		if(content.indexOf('jobid') != -1){
			jid = content.replace('jobid=','');
			if(_this.adminjob[jid]){
				chat.job = _this.adminjob[jid];
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