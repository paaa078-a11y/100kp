var player = null,
	playreload = null,
	playing = false,
	setval = null,
	page = 1,
	lastid = '',
	ismore = true,
	chatloading = false;

var u = navigator.userAgent;
var x5_type = 'h5';
if (/(iPhone|iPad|iPod|iOS)/i.test(u)) { //判断iPhone|iPad|iPod|iOS
	x5_type = 'h5';
} else if (/(Android)/i.test(u)) { //判断Android
	x5_type = 'h5-page';
}
var clientHeight = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
var clientWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;

$(function(){
	// 直播
	livePlay();
	// 点播
	playered();
	//用户变化屏幕方向时调用
	$(window).bind('orientationchange', function() {
		if(playing){
			palyOrient();
		}else{
			orient();
		}
	});
})

function livePlay() {
	if(livestatus == 3){
		if (!document.getElementById('live_video')) {
			$('#livediv').append('<video id="live_video" width="414" height="270" preload="auto" playsinline webkit-playsinline></video>');
		}
		if (player) {
			player.dispose();
			player = null;
		}
		setTimeout(function(){
			var live_hls = document.getElementById('live_hls').value,
					live_poster = document.getElementById('live_poster').value,
					img = $("#poster").find('img').attr('src'),
					poster = img ? img : live_poster;
				player = new TCPlayer('live_video', {
				    "sources": [{src: live_hls}],
					"autoplay": true,
					"poster": poster
				});
				player.on('loadstart', function(){
				    $('.video-js').css({
			          'width':'10rem',
			          "height":"5.6rem"
				    });
				    $('.video-poster').css({
			          'width':'10rem',
			          "height":"5.7rem"
				    });
				})
				player.on('play', function(){
				    $('.video-js').css({
			          'width':'10rem',
			          "height":"5.7rem"
				    });
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
		},100);
	}
}

function playered() {
	if (document.getElementById('playered')) {
		var url = $("#playurl").val();
		if(url){
			$("#playered").html('');
			// 实例化播放器
			player = new QPlayer({
				url: url,
				container: document.getElementById("playered"),
				autoplay: true,
				loggerLevel: 3,
				defaultViewConfig: {
					settingsIcon: mbstyle +'/images/qiniuset.png'
				}
			});
			player.on('play', function() {
				//用户变化屏幕方向时调用
				$(window).bind('orientationchange', function() {
					palyOrient();
				});
			});
		}
	}
}

// 播放时处理横屏和竖屏
function palyOrient() {
	if(typeof commonHide != 'undefined'){
		commonHide();
	}
	if (window.orientation == 90 || window.orientation == -90) {
		//横屏
		// 直播
		if ($("#live_video").length > 0) {
			player.requestFullscreen(true);
		}
		return false;
	} else if (window.orientation == 0 || window.orientation == 180) {
		//竖屏
		// 直播
		if ($("#live_video").length > 0) {
			player.requestFullscreen(false);
			$(window).unbind();
			orient();
		}
		return false;
	}
}
// 处理横屏和竖屏
function orient() {
	if(typeof commonHide != 'undefined'){
		commonHide();
	}
	if (window.orientation == 90 || window.orientation == -90) {
		//横屏
		// 直播
		if ($("#live_video").length > 0) {
			setTimeout(function(){
				var clientHeight = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
				var hpheight = clientHeight - 51 + 'px';
				$("#livebody").css('height',hpheight);
				$("#xjhdiv").attr('style','width:60%;margin:0 auto;');
				$(".vcp-player").css({"width":"100%","height":hpheight});
				$("#live_video").css({"width":"100%","height":hpheight});
				$('.xjh_show_wid').css({"width":"60%","margin":"0 auto"});
				$(".show_box_chat_input").css({"width":"60%","margin":"0 auto","background":"#fff","box-shadow":"none"});
			},200)
		}
	} else if (window.orientation == 0 || window.orientation == 180) {
		//竖屏
		// 直播
		if ($("#live_video").length > 0) {
			$("#livebody").css('height','220px');
			$(".vcp-player").css({"width":"100%","height":'220px'});
			$("#live_video").css({"width":"100%","height":'220px'});
			$("#xjhdiv").removeAttr('style');
			$('.xjh_show_wid').removeAttr('style');
			$(".show_box_chat_input").removeAttr('style');
		}
		return false;
	}
}
if(livestatus == 1){
	var serverTime = new Date().getTime(),
		djsTimer = null;
	// 监测浏览器息屏和可见
	var hiddenProperty = 'hidden' in document ? 'hidden' : 'webkitHidden' in document ? 'webkitHidden' : 'mozHidden' in document ? 'mozHidden' :  null;
	// 不同浏览器的事件名
	var visibilityChangeEvent = hiddenProperty.replace(/hidden/i, 'visibilitychange');
	var onVisibilityChange = function(){
		var nowTime = new Date().getTime();
		if(!document[hiddenProperty]){
			if(serverTime > 0 && (nowTime - serverTime > 60000)){
				clearTimeout(djsTimer);
				serverTime = nowTime;
				liveUtil();
			}
		}else{
			serverTime = nowTime;
			if(player){
				player.pause();
			}
		}
	}
	document.addEventListener(visibilityChangeEvent, onVisibilityChange);
	// 处理倒计时
	liveUtil();
}

function liveUtil(){
    var stime = parseInt($("#live_stime").val()) * 1000;

	var time = stime -serverTime;
	if (serverTime < stime) {
		var vm = new Vue({
	        el: '#waitshow',
	        data: {
		        time:time,
	        },
	        methods: {
			    onFinish() {
					//倒计时结束
					if (!document.getElementById('live_video')) {
						$("#xjhbox").css('height', 'calc(100% - 5.733rem)');
						$('#poster').hide();
						$('#waitbox').remove();
						getXjhlive();
					}
			    }
			}
	    });
	}else {
		getXjhlive();
	}
}
function xjhyy(id) {
    yunvue.$data.xjhyyQrcode = '<img src="'+wapurl+'index.php?c=xjhlive&a=xjhyyQrcode&id=' + id +
        '" width="120" height="120">';
    yunvue.$data.xjhyyQrcodeBoxShow = true;
}
function subxjh(id) {
	showLoading();
	var url = wapurl + '/index.php?c=xjhlive&a=xjhSubcribe';
	$.post(url, {
		xjhid: id
	}, function(data) {
		hideLoading();
		var res=eval('('+data+')');
		if (res.error == 9) {
			if(isweiixn){
				if (data.wxshow) {
					// 未绑定微信的，提示绑定微信
					showLoading();
					$.post(getwxurl, {
						t: 1
					}, function(wdata) {
						hideLoading();
						if (wdata == 0) {
							showToast("预约成功", 2, function() {
								window.location.reload();
							});
						} else {
							yunvue.$data.ewmurl = wdata;
							yunvue.$data.ewmBox = true;
							
							setval = setInterval(function(){
								$.post(getwxstatusurl,{t:1},function(data){
									var res=eval('('+data+')');
									if(res.msg!=''){
										window.location.reload();
									}
								});
							}, 2000); 
						}
					});
				}else{
					// 已绑定微信的，显示公众号二维码
					yunvue.$data.ewmurl = ewmurl;
					yunvue.$data.ewmBox = true;
				}
			}else{
				showToast("预约成功", 2, function() {
					window.location.reload();
				});
			}
		}else if(res.error == 8){
			showToast("请登录个人用户");
		}else if(res.error == 7){
			showToast("您已预约，请勿重复预约");
		}else {
			showToast("数据异常请重试");
		}
	});
}
// 检查宣讲会直播状态
function getXjhlive(){
	$.post(wapurl+"index.php?c=xjhlive&a=getXjhlive",{id: xjhid},function(data){
		if(data){
			var res = eval('(' + data + ')');
			if(parseInt(res.livestatus) > 0){
				livestatus = res.livestatus;
				yunvue.$data.livestatus = res.livestatus;
				if(res.livestatus == 3){
					// 直播中或导播台开启到了直播时间
					if(res.hls){
						$("#live_hls").val(res.hls);
					}
					if (document.getElementById('live_video')) {
						livePlay();
					}else{
						$("#xjhbox").css('height', 'calc(100% - 5.733rem)');
						$('#poster').hide();
						$('#waitbox').remove();
						if(!document.getElementById('livediv')){
							$('#livebox').append('<div class="liveTopImg" id="livediv"></div>');
						}
						livePlay();
					}
				}else if(res.livestatus == 2){
					// 直播结束
					if(player){
						player.dispose();
						player = null;
					}
					//var html = $("#codvideo").html();
					//$("#livebody").html(html);
				}
			}
		}
	});
}
var formDisabled = false;
var formData = {};
var yzmcode = '';
var pickerType = '';
var pickerKey = 0;
var timestamp = new Date();
var year = timestamp.getFullYear();
var workObj = {
	name: '',
	title: '',
	content: '',
	sdate: '',
	edate: '',
	totoday: 0
};
var eduObj = {
	name: '',
	specialty: '',
	sdate: '',
	edate: '',
	edu: '',
	eduid: 0
};
var yunvue = new Vue({
		el: '#xjhbox',
		data: {
			ewmBox: false,
			ewmurl: '',
            xjhyyQrcodeBoxShow: false,
            xjhyyQrcode: '正在获取二维码...',
			boxtab: 'chat',
			chatlist: [],
			comlist: [],
			comBox: false,
			explain: {},
			comdisplay: {},
			jobBox: false,
			jobdisplay: {},
			xjhbody: '',
			livestatus: livestatus,
			chatOne: true,
			faceOne: false,
			notice: '',
			applyjobid: 0,
			info: {
				resume_create_exp: resume_create_exp,
				resume_create_edu: resume_create_exp
			},
			resumeBox: false,
			codeview: true,
			flag: 120,
			sexShow: false,
			sex: sexData,
			sexIndex: 0,
			eduShow: false,
			eduIndex: 0,
			edu: [],
			expShow: false,
			expIndex: 0,
			exp: [],
			birthdayShow: false,
			currentYear: new Date(1988, 7, 8),
			minYear: new Date(1960, 0, 1),
			maxYear: new Date(year-16,12,0),
			verify_token: '',
			varify_token: '',
			workBox: false,
			eduBox: false,
			workList: [],
			eduList: [],
			timeData: [],
			timeBox: false,
			contentBox: false,
			contentTitle: '',
			content: '',
			yzmBox: false,
			code_url: '',
			nochat: nochat,
			vistor_nochat: vistor_nochat,
			zan: zan,
		},
		created() {
			$("#xjhbox").removeClass('none');
			this.getcomlist();
			this.xjhbody = $("#xjhbody").html();
			
			loadJs();
			this.moreChat();
			let self = this;
		},
		computed: {
			sendFocus(){
				return !this.chatOne && !this.faceOne ? true : false
			}
		},
		methods: {
			ewmClose(){
				window.location.reload();
			},
            xjhyyClose(){
                this.xjhyyQrcodeBoxShow = false;
                window.location.reload();
            },
			boxClick: function(e){
				this.boxtab = e;
			},
			getcomlist: function(keyword = '') {
			    let self = this;
			    $.post(wapurl + 'index.php?c=xjhlive&a=getcomlist', { xid: xjhid, keyword: keyword }, function(res) {
			        self.comlist = res.comlist;
					self.explain = res.explain;
					self.notice = res.notice;
			    }, 'json');
			},
			comClick: function(k){
				this.comdisplay = this.comlist[k];
				this.comBox = true;
			},
			comClose: function(){
				this.comBox = false;
			},
			jobClick: function(k){
				this.jobdisplay = this.comdisplay.joblist[k];
				this.jobBox = true;
			},
			chatJob: function(id){
				let self = this;
				this.comlist.forEach(function(item,index){
					if(item.joblist && item.joblist.length > 0){
						item.joblist.forEach(function(v){
							if(v.id == id){
								self.jobdisplay = v;
							}
						})
					}
				})
				this.jobBox = true;
			},
			jobClose: function(){
				this.jobBox = false;
			},
			liveComShow: function(){
				if(this.livestatus == 1){
					return showToast('直播还未开始');
				}else if(this.livestatus == 2){
					return showToast('直播已结束');
				}
				this.comdisplay = this.explain;
				this.comBox = true;
			},
			chatClick: function(){
				this.chatOne = false;
			},
			faceClick: function(){
				this.chatOne = false;
				this.faceOne = true;
				setTimeout(function(){
					face();
				},10);
			},
			sendChat: function(){
				if(this.nochat == '1'){
					return showToast('全员禁言中，无法发言');
				}else if(this.nochat == '0' && this.vistor_nochat == '1' && usertype == ''){
					return showConfirm('登录后才能发言，前往登录？', function(){
						window.location.href = wapurl + 'index.php?c=login';
					});
				}
				this.chatOne = true;
				this.faceOne = false;
				send();
			},
			apply: function(jobid){
				this.applyjobid = jobid;
				if(uid != ''){
					var that = this;
					showLoading()
					$.get(wapurl + "index.php?c=job&a=comapply&type=sq&id="+jobid,function (data) {
						hideLoading();
						if (data.state == 1) {
							pleaselogin('您还未登录个人账号，是否登录？', wapurl+'index.php?c=login');
						} else if (data.state == 2) {
							showToast('只有个人用户才能投递');
							return false;
						} else if (data.state == 7 || data.state == 101 || data.state == 102) {
						
							showConfirm(data.msg, function () {
								location.href = data.wapurl;
							}, '取消', '完善简历');
						
						} else if (data.url) {
							if (data.url == '1') {
								url = location.href;
							}
							showToast(data.msg, 2, function () {
								location.href = data.url;
							});
						} else {
							showToast(data.msg, 2);
						}
					},'json');
				}else{
					this.resumeBox = true;
				}
			},
			sexConfirm(e) {
				this.sexShow = false;
				this.info.sex = e.value;
				this.info.sex_n = e.text;
			},
			birthdayConfirm(e) {
				this.birthdayShow = false;
				this.info.birthYear = timeFormat(e);
				this.info.birthday = timeFormat(e);
			},
			eduPicker: function(key) {
				if (typeof(key) === 'number') {
					// 教育经历中的picker
					pickerType = 'eduList';
					pickerKey = key;
					eduPicker(this.eduList[key].eduid);
				} else {
					pickerType = '';
					eduPicker(this.info.edu);
				}
			},
			eduConfirm(e) {
				this.eduShow = false;
				if (pickerType == 'eduList') {
					// 教育经历中的picker
					this[pickerType][pickerKey].edu = e.text;
					this[pickerType][pickerKey].eduid = e.value;
				} else {
					this.info.edu = e.value;
					this.info.edu_n = e.text;
				}
			},
			expPicker: function() {
				expPicker(this.info.exp);
			},
			expConfirm(e) {
				this.expShow = false;
				this.info.exp = e.value;
				this.info.exp_n = e.text;
			},
            // 初始化验证码校验
            initYzmCheck: function(){
                var that = this;
                var codesear = new RegExp('注册会员');
                if (codesear.test(code_web)) {
                    if (code_kind == 1) {
                        if (!this.info.authcode) {
                            showToast('请填写图片验证码');
                            return false;
                        }
                    } else if (code_kind > 2) {
                        that.verify_token = $('input[name="verify_token"]').val();
                        if (that.verify_token == '') {
                            if (code_kind == 6) {
                                $("#bind-captcha").trigger("click");
                            } else {
                                $("#bind-submit").trigger("click");
                            }
                            return false;
                        }
                        that.verify_str = $('input[name="verify_str"]').val();
                    }
                }
                return true;
            },
			getcode: function() {
				if (!this.info.telphone) {
					return showToast('请输入手机号');
				} else if (isjsMobile(this.info.telphone) == false) {
					return showToast('请填写正确的手机号');
				}
				var self = this;
				if (!self.initYzmCheck()) {// 防止弹出验证码时继续向下执行
					return false;
				}
				var obj = {
					moblie: this.info.telphone,
					code: this.info.authcode,
					verify_token: self.verify_token,
					verify_str: self.verify_str,
					noblur: 1
				};
				showLoading();
				$.post(wapurl + "?c=ajax&a=regcode", obj, function(res) {
					hideLoading();
					if (res.errcode) {
						showModal('手机号已被使用');
						checkCode('vcode_img');
					} else {
						if (res.error != 1) {
							showModal(res.msg);
							self.codeview = true;
							checkCode('vcode_img');
						} else {
							self.countdown();
						}
					}
				}, 'json');
			},
			countdown: function() {
				var that = this;
				if (that.flag > 0) {
					that.codeview = false;
					setTimeout(function() {
						that.flag = that.flag - 1;
						that.countdown();
					}, 1000);
				} else {
					that.codeview = true;
					that.flag = 120;
				}
			},
			firstSubmit: function() {
				var evalue = getFormValue('firstSubmit');
				var that = this;

				if (!evalue.uname) {
					showToast('请填写姓名');
					return false;
				} else {
					if (parseInt(resumename) && parseInt(resumename) > 0 && !isChinaName(evalue.uname)) {
						showToast('姓名请输入2-6位汉字');
						return false;
					}
				}
				if (!evalue.sex) {
					showToast('请选择性别');
					return false;
				}
				if (!evalue.birthday) {
					showToast('请选择出生年月');
					return false;
				}
				if (!evalue.edu) {
					showToast('请选择学历');
					return false;
				}
				if (!evalue.exp) {
					showToast('请选择工作经验');
					return false;
				}
				if (!evalue.telphone) {
					showToast('请填写手机号码');
					return false;
				}
				if (isjsMobile(evalue.telphone) == false) {
					showToast('手机号码格式错误');
					return false;
				}
				if (!evalue.password) {
					showToast('请填写登录密码');
					return false;
				}
                if (!that.initYzmCheck()) {// 防止弹出验证码时继续向下执行
                    return false;
                }
				formData = evalue;
				formData.verify_token = that.verify_token;
				formData.verify_str = that.verify_str;
				if (this.info.resume_create_edu != 1 && this.info.resume_create_exp != 1 && this.info.resume_create_project != 1) {
					this.saveForm();
				} else {
					if (this.info.resume_create_exp == 1) {
						// 工作经历必填
						this.workBox = true;
						if (this.workList.length == 0) {
							this.workList.push(deepClone(workObj));
						}
					} else if (this.info.resume_create_edu == 1) {
						// 教育经历必填
						this.eduBox = true;
						if (this.eduList.length == 0) {
							this.eduList.push(deepClone(eduObj));
						}
					}
				}
			},
			saveForm: function() {
				var self = this;
				if (!formDisabled) {
					showLoading();
					formData.source = 24;
					formData.jobid = this.applyjobid;
					formData.verify_token = self.verify_token;
					formData.verify_str = self.verify_str;
					$.post(wapurl + "?c=ajax&a=temporaryresume", formData, function(data) {
						if (data.url) {
							// 跳转前，阻止再次提交，防止重复提交
							formDisabled = true;
							showToast(data.msg, 2, function() {
								window.location.reload();
							});
						} else {
							showToast(data.msg, 2, function() {
								if (data.error && data.error == 106) {
									// 图片验证码错误
									if (self.info.resume_create_edu == 1 || self.info.resume_create_exp == 1) {
										// 需要填工作经历等，图片验证码，不需要返回到第一步，直接在弹出框填写
										self.yzmBox = true;
										setTimeout(function() {
											self.code_url = code_url;
										}, 100);
									} else {
										checkCode('vcode_img');
									}
								} else if (data.error && data.error == 107) {
									$("#bind-submit").trigger("click");
								}
							});
						}
					}, 'json');
				}
			},
			// 时间picker显示
			timeShow(type, key, time) {
				pickerType = type;
				pickerKey = key;
				pickerTime = time;

				this.timeData = timePicker(type, time, this[pickerType][pickerKey][pickerTime]);
				let self = this;
				setTimeout(function() {
					self.timeBox = true;
				})
			},
			// 时间picker滚动
			timeChange: function(e) {
				var f = e.getValues();
				if (f[0] == '至今') {
					this.timeData[1].values = [];
				} else {
					this.timeData[1].values = workMonth;
				}
			},
			// 时间picker确认
			timeConfirm: function(e) {
				this.timeBox = false;
				if (e[0] == '至今') {
					var date = e[0];
					this[pickerType][pickerKey]['totoday'] = 1;
				} else {
					var date = e.join('-');
					if (pickerType == 'workList' && pickerTime == 'edate') {
						this[pickerType][pickerKey]['totoday'] = 0;
					}
				}
				this[pickerType][pickerKey][pickerTime] = date;
				this.timeData = [];
			},
			showContent: function(type, key) {
				pickerType = type;
				pickerKey = key;
				if (type == 'workList') {
					this.contentTitle = '工作内容';
					this.content = this.workList[key].content;
				}
				this.contentBox = true;
			},
			contentSubmit: function() {
				this[pickerType][pickerKey]['content'] = this.content;
				this.contentBox = false;
			},
			skipPage: function(type) {
				// 跳过
				if (type == 'work') {
					this.skipOrSubmitOfWork();
				} else if (type == 'edu') {
					this.saveForm();
				}
			},
			workSubmit: function() {
				// 工作经历下一步
				var field = getFormValue('workSubmit');
				for (var i = 0; i < this.workList.length; i++) {
					var st = Date.parse(field['worksdate_' + i]) / 1000,
						ed = Date.parse(field['workedate_' + i]) / 1000;
					if (!field['workname_' + i]) {
						showModal('请填写工作经历的公司名称');
						return false;
					} else if (!field['worktitle_' + i]) {
						showModal('请填写工作经历的担任职位');
						return false;
					} else if (!field['worksdate_' + i]) {
						showModal('请选择工作经历的入职时间');
						return false;
					} else if (!field['workedate_' + i] && parseInt(field['totoday_' + i]) == 0) {
						showModal('请选择工作经历的离职时间');
						return false;
					} else if (field['worksdate_' + i] && field['workedate_' + i] && st > ed) {
						showModal('工作经历的离职时间不能小于入职时间');
						return false;
					}
					field['workcontent_' + i] = this.workList[i].content;

				}
				field['iscreateexp'] = 1;
				Object.assign(formData, field);
				this.skipOrSubmitOfWork();
			},
			// 工作经历跳过
			skipOrSubmitOfWork: function() {
				if (this.info.resume_create_edu != 1) {
					this.saveForm();
				} else {
					this.eduBox = true;
					if (this.eduList.length == 0) {
						this.eduList.push(deepClone(eduObj));
					}
				}
			},
			eduSubmit: function() {
				// 教育经历下一步
				var field = getFormValue('eduSubmit');
				for (var i = 0; i < this.eduList.length; i++) {
					var st = Date.parse(field['edusdate_' + i]) / 1000,
						ed = Date.parse(field['eduedate_' + i]) / 1000;
					if (!field['eduname_' + i]) {
						showModal('请填写教育经历的学校名称');
						return false;
					} else if (!field['eduid_' + i]) {
						showModal('请填写教育经历的学历');
						return false;
					} else if (!field['edusdate_' + i]) {
						showModal('请选择教育经历的入学时间');
						return false;
					} else if (!field['eduedate_' + i]) {
						showModal('请选择教育经历的毕业时间');
						return false;
					} else if (field['edusdate_' + i] && field['eduedate_' + i] && st > ed) {
						showModal('教育经历的毕业时间不能小于入学时间');
						return false;
					}
				}
				field['iscreateedu'] = 1;
				Object.assign(formData, field);
				this.saveForm();
			},
			// 验证码输入检测，达到长度就提交
			yzmInput(e) {
				yzmcode += e.data;
				if (yzmcode.length == code_strlength) {
					this.yzmBox = false;
					formData.authcode = yzmcode;
					yzmcode = '';
					this.saveForm();
				}
			},
			moreChat(){
				chatloading = true;
				let self = this;
				$.post(wapurl + "index.php?c=chat&a=getXjhChatPage", {'xjhid': xjhid,'page': page,'lastid': lastid}, function(res){
					chatloading = false;
					ismore = res.ismore;
					var cl = res.list.length;
					if(cl>0){
						let list = [];
						for(var i in res.list){
							res.list[i].content = rexContent(res.list[i].content)
							list.push(res.list[i]);
						}
						let newlist = list.reverse();
						self.chatlist = newlist.concat(self.chatlist);
						
						page = page + 1;
						if(page == 2){
							// 初次加载首条id
							lastid = res.list[0]['chatid'];
							setTimeout(function(){
								// 页面滚动
								self.scrollTo();
							},100);
						}
					}
					$("#chat_content").scroll(function(){
						var cs = document.getElementById('chat_content').scrollTop;
						if(cs == 0 && ismore && !chatloading){
							self.moreChat();
						    setTimeout(function(){
						        document.getElementById('chat_content').scrollTop = 21;
						    },50)
						}
					})
				}, 'json');
			},
			// 页面滚动
			scrollTo(ypos) {
				var clientHeight = document.documentElement.clientHeight;
				var ypos = document.getElementById('chat_content').scrollHeight;
				if((ypos + 300) > clientHeight){
					// 兼容各种浏览器
					document.getElementById('chat_content').scrollTop  = ypos;
				}
			}
		}
	});
