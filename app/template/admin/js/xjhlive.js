var scrollHeight = 0,
	playreload = null,
	ping_timer = null,
	pytoken = localStorage.getItem('pytoken'),
	player = null;

// 页面滚动
function chatScrollTo(ypos) {
	var clientHeight = document.getElementById('chat_content').clientHeight;
	if(ypos > clientHeight){
		// 兼容各种浏览器
		document.getElementById('chat_content').scrollTop = ypos;
	}
}
// 转义内容
function rexContent(content){
	content = (content||'').replace(/&(?!#?[a-zA-Z0-9]+;)/g, '&amp;')
    .replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/'/g, '&#39;').replace(/"/g, '&quot;') //XSS
	.replace(/face\[([^\s\[\]]+?)\]/g, function(face){  //转义表情
		var alt = face.replace(/^face/g, '');
		return '<img class="chat_bq_img" alt="'+ alt +'" title="'+ alt +'" src="' + faces([alt]) + '">';
	})
	return content;
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
//在焦点处插入内容
function focusInsert(obj, str, nofocus){
    var result, val = obj.value;
    nofocus || obj.focus();
    if(document.selection){ //ie
		result = document.selection.createRange();
		document.selection.empty();
		result.text = str;
		return ''
    } else {
		result = [val.substring(0, obj.selectionStart), str, val.substr(obj.selectionEnd)];
		nofocus || obj.focus();
		return result.join('');
    }
}

// 格式化时间戳
function timeFormat(sendtime) {
	var nowtime = new Date();
	if (sendtime) {
		sendtime = new Date(sendtime);
	} else {
		sendtime = nowtime;
	}
	var year = sendtime.getFullYear(),
		nowyear = nowtime.getFullYear();
		month = sendtime.getMonth(),
		day = sendtime.getDate(),
		hour = sendtime.getHours(),
		minutes = sendtime.getMinutes();

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
	if(year != nowyear){
		time = year + '-' + time;
	}
	return time;
}
// 获取文档完整的高度
function getScrollHeight() {
	return Math.max(document.body.scrollHeight, document.documentElement.scrollHeight);
}

// 直播
function livePlay(live_hls, live_poster, width, height){
    // debugger
	if (!document.getElementById('live_video')) {
		$('#livebody').append('<video id="live_video" preload="auto" playsinline webkit-playsinline></video>');
	}
	if (player) {
		player.dispose();
		player = null;
	}
	setTimeout(function() {
		if(document.getElementById('live_video')){
			var body = document.body,
				poster = live_poster;
			player = new TCPlayer('live_video', {
		        "sources": [{src: live_hls}],
				"autoplay": true,
				"width": width,
				"height": height,
				"poster": poster
			});

			var playerint = null;
			player.on('error', function(error) {
				player.dispose();
				player = null;
				if(!playerint){
					playerint = setInterval(function(){
						livePlay();
					},3000);
				}
			});
			player.on('playing', function(error) {
				if(playerint){
					clearInterval(playerint);
					playerint = null;
				}
			});
			// 播放器重载
			if(playreload){
				clearInterval(playreload);
				playreload = null;
			}
			playreload = setInterval(function(){
				if(player && player.player_ == null){
					livePlay();
				}
			},3000);
		}
	}, 100);
}
