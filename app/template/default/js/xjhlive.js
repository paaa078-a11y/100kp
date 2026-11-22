var player = null,
	playreload = null,
    page = 1,
    lastid = '',
    ismore = true,
    chatloading = false;
$(function() {
    // 点播
    playered();
	
    if (livestatus == '3') {
        if (!document.getElementById('live_video')) {
            $('#livebody').append('<div class=""><video id="live_video" preload="auto" playsinline webkit-playsinline></video></div>');
            // 直播
            livePlay();
        }
    }
});

layui.use(['carousel'], function() { //layui 轮播  test1 test2 
    var carousel = layui.carousel;
    carousel.render({
        elem: '#cover',
        width: '880px',
        height: '495px',
        indicator: 'none'
    });
});
if (livestatus == 1) {
    var serverTime = new Date().getTime(),
        djsTimer = null;
    // 监测浏览器息屏和可见
    var hiddenProperty = 'hidden' in document ? 'hidden' : 'webkitHidden' in document ? 'webkitHidden' : 'mozHidden' in document ? 'mozHidden' : null;
    // 不同浏览器的事件名
    var visibilityChangeEvent = hiddenProperty.replace(/hidden/i, 'visibilitychange');
    var onVisibilityChange = function() {
        var nowTime = new Date().getTime();
        if (!document[hiddenProperty]) {
            if (serverTime > 0 && (nowTime - serverTime > 60000)) {
                clearTimeout(djsTimer);
                serverTime = nowTime;
                liveUtil();
            }
        } else {
            serverTime = nowTime;
        }
    }
    document.addEventListener(visibilityChangeEvent, onVisibilityChange);
    // 处理倒计时
    liveUtil();
}

function liveUtil() {
    layui.use('util', function() {
        var util = layui.util;
        var stime = $("#live_stime").val();
        var endTime = parseInt(stime) * 1000;

        if (serverTime < endTime) {
            util.countdown(endTime, serverTime, function(date, serverTime, timer) {
                djsTimer = timer;
                var str = "<span>" + date[0] + "</span ><b>天</b> <span>" + date[1] +
                    "</span> <b>时</b> <span>" + date[2] + "</span> <b>分</b> <span>" + date[3] + "</span> <b>秒</b>";
                layui.$('#countdown').html(str);
                if (date[0] == 0 && date[1] == 0 && date[2] == 0 && date[3] == 0) {

                    $('#cover').hide();
                    $("#liveremind").remove();
                    //倒计时结束
                    clearTimeout(timer);
                    if (!document.getElementById('live_video')) {
                        $('#livebody').append('<div class=""><video id="live_video" preload="auto" playsinline webkit-playsinline></video></div>');
                        getXjhlive();
                    }
                }
            });
        } else {
            getXjhlive();
        }
    });
}
var tcsetval,
    tcsetwout;

function subxjh(id, uid) {
    if (uid) {
        $.post('index.php?m=xjhlive&c=xjhSubcribe', {
            xjhid: id
        }, function(data) {
            var res = eval('(' + data + ')');
            if (res.error == 9) {
                if (res.wxshow) {
                    // 未绑定微信的，提示绑定微信
                    layer.msg('恭喜您预约成功', 2, 9, function() {
                        $.layer({
                            type: 1,
                            title: '绑定微信',
                            border: [10, 0.3, '#000', true],
                            area: ['340px', 'auto'],
                            page: {
                                dom: "#wxcontent"
                            },
                            close: function() {
                                clearInterval(tcsetval);
                                window.location.reload();
                            }
                        });
                    });
                    tcwxlogincode();
                } else {
                    layer.msg("恭喜您预约成功", 2, 9, function() {
                        window.location.reload();
                    });
                }
            } else if (res.error == 8) {
                layer.msg("请登录个人用户", 2, 8);
            } else if (res.error == 7) {
                layer.msg("您已预约，请勿重复预约", 2, 8);
            } else {
                layer.msg("数据异常请重试", 2, 8);
            }
        });
    } else {
        showlogin();
    }
}

function tcwxlogincode() {

    $.post(getwxurl, {
        t: 1
    }, function(wdata) {
        if (wdata == 0) {
            $('#wx_login_qrcode_spview').html('二维码获取失败..');
        } else {
            $('#wx_login_qrcode_spview').html('<img src="' + wdata + '" width="180" height="180">');
            tcsetval = setInterval(function() {
                $.post(getwxstatusurl, {
                    t: 1
                }, function(data) {

                    var data = eval('(' + data + ')');

                    if (data.url != '' && data.msg != '') {
                        clearInterval(tcsetval);
                        tcsetval = null;
                        layer.msg(data.msg, 2, 9, function() {
                            window.location.reload();
                        });
                    } else if (data.url != '') {
                        window.location.reload();
                    }
                });
            }, 2000);
            if (tcsetwout) {
                clearTimeout(tcsetwout);
                tcsetwout = null;
            }
            tcsetwout = setTimeout(function() {
                clearInterval(tcsetval);
                tcsetval = null;
                var tcwx_sx = $("#tcwx_sx").html();
                $('#wx_login_qrcode_spview').html(tcwx_sx);
            }, 300 * 1000);
        }
    });
}

function livePlay() {
	if (!document.getElementById('live_video')) {
		$('#livebody').append('<div class=""><video id="live_video" preload="auto" playsinline webkit-playsinline></video></div>');
	}
	if (player) {
		player.dispose();
		player = null;
	}
    setTimeout(function(){
		var body = document.body,
		    live_hls = document.getElementById('live_hls').value,
		    live_poster = document.getElementById('live_poster').value,
		    img = $("#stopLive").find('img').attr('src'),
		    poster = img ? img : live_poster;
		player = new TCPlayer('live_video', {
		    "sources": [{src: live_hls}],
		    "autoplay": true,
		    "width": '880',
		    "height": '495',
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
	},100);
}
function playered() {
    if (document.getElementById('playered')) {
        var url = $("#playurl").val();
        // 实例化播放器
        player = new QPlayer({
            url: url,
            container: document.getElementById("playered"),
            autoplay: true,
            loggerLevel: 3,
            defaultViewConfig: {
                settingsIcon: mbstyle + '/images/qiniuset.png'
            }
        });
    }
}

function getUserZd() {
    window.location.href = weburl + "/member/index.php?c=expect&act=add";
}
// 检查宣讲会直播状态
function getXjhlive() {
    $.post(weburl + "/index.php?m=xjhlive&c=getXjhlive", { id: xjhid }, function(res) {
        if (res) {
            if (parseInt(res.livestatus) > 0) {
				livestatus = res.livestatus;
                if (res.livestatus == 3 || res.iscaster == 1) {
                    // 直播中或导播台开启到了直播时间
                    if (res.hls) {
                        $("#live_hls").val(res.hls);
                    }
                    if (document.getElementById('live_video')) {
                        if (!player || player.player_ == null) {
                            livePlay();
                        }
                    } else {
                        $('#cover').hide();
                        $("#liveremind").remove();
                        livePlay();
                    }
                } else if (res.livestatus == 2) {
                    // 直播结束
                    if (player) {
                        player.destroy();
                    }
                    var html = $("#codvideo").html();
                    $("#livebody").html(html);
                }
            }
        }
    }, 'json');
}
// 直播介绍和参会企业
var comvue = new Vue({
    el: '#xjhbox',
    data: {
        boxtab: 'xjh',
        xjhbody: '',
        comlist: [],
		comShow: ''
    },
    created: function() {
		$("#xjhbox").removeClass('none');
        this.getcomlist();
        this.xjhbody = $("#xjhbody").html();

        this.moreChat();
    },
    methods: {
        getcomlist: function(keyword) {
            let self = this;
            $.post(weburl + '/index.php?m=xjhlive&c=getcomlist', { xid: xjhid, keyword: keyword }, function(res) {
                self.comlist = res.comlist;
				self.comShow = res.explainid;
				app.$data.notice = res.notice;
            }, 'json');
        },
        boxClick: function(e) {
            this.boxtab = e;
            if (e == 'com') {
                setTimeout(function() {
                    $(".videoSearchButton").click(function() {
                        $(".videoSearchInput").toggleClass("videoSearchInputCur");
                        $(this).toggleClass("videoSearchButtonCur");
                    });
                    $(".videoSearchClear").click(function() {
                        $(".videoSearchButton").removeClass("videoSearchButtonCur");
                        $(".videoSearchInput").removeClass("videoSearchInputCur");
                    });
                }, 100);
            }
        },
        comSearch: function() {
            var keyword = $('#comkeyword').val();
            if (keyword == '') {
                return layer.msg('请输入关键词', 2, 8);
            }
            this.getcomlist(keyword);
        },
        clearSearch: function() {
            $('#comkeyword').val('');
            this.getcomlist();
        },
		comClick: function(k){
			this.comShow = this.comlist[k].uid;
		},
        moreChat(){
            chatloading = true;
            let self = this;
            $.post(weburl + "/index.php?m=chat&c=getXjhChatPage", {'xjhid': xjhid,'page': page,'lastid': lastid}, function(res){
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
                    app.$data.chatlist = newlist.concat(app.$data.chatlist);
                    
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
            var clientHeight = document.getElementById('chat_content').clientHeight;
            var ypos = document.getElementById('chat_content').scrollHeight;
            if(ypos > clientHeight){
                // 兼容各种浏览器
                document.getElementById('chat_content').scrollTop  = ypos;
            }
        }
    }
});
		