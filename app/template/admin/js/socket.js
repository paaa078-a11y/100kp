var islink = 1,
	ping_timer = null;
window.socketObj = null;
// 初始化socket
function socketInit(succ){
    if ("WebSocket" in window) {
    	if (!window.socketObj) {
            webSocket(socketUrl,succ);
		}
    } else {
        console.log("您的浏览器不支持聊天!");
    }
}
function receiveMessage(succ){
	if (!window.socketObj) {
	    socketInit(succ);
	}else{
		window.socketObj.onmessage = function(event) {
			var e = JSON.parse(event.data);
			switch (e.type) {
				case 'getMessage':
					typeof succ === 'function' && succ(e.data);
					break;
				case 'error':
					islink = 2;
					if(ping_timer){
						clearInterval(ping_timer);
						ping_timer = null;
					}
					break;
				case 'xjhMessage':
					typeof succ === 'function' && succ(e);
					break;
				case 'startLive':
					typeof succ === 'function' && succ(e);
					break;
				case 'stopLive':
					typeof succ === 'function' && succ(e);
					break;
			}
		};
	}
}
function webSocket(socketUrl,succ){
	if(islink == 1){
		if(ping_timer){
			clearInterval(ping_timer);
			ping_timer = null;
		}
		window.socketObj = new WebSocket(socketUrl);
		
		window.socketObj.onopen = function() {
			var ping = {
				type: 'ping'
			};
			ping_timer = setInterval(function() {
				if (window.socketObj){
					if(window.socketObj.readyState == 1){
						window.socketObj.send(JSON.stringify(ping));
					}else{
						window.socketObj.close();
					}
				}
			}, 30000);
			httpPost('m=crm&c=crm_chat&a=room',{}).then((result) => {
				var data = result.data;
				var res = data.data;
				if (data.error == 0) {
					if(res.data){
						var bindData = res.data;
						var bind = {
							type: 'bind',
							data: bindData
						}
						window.socketObj.send(JSON.stringify(bind));
					}
				}
			}).catch(function (e) {
				console.log(e)
			})
		};

		window.socketObj.onerror = function() {
			islink = 2;
			window.socketObj = null;
			
			if(ping_timer){
				clearInterval(ping_timer);
				ping_timer = null;
			}
		};

		window.socketObj.onclose = function() {
			window.socketObj = null;
			
			if(ping_timer){
				clearInterval(ping_timer);
				ping_timer = null;
			}
		};
		
		//捕捉socket端发来的事件
		receiveMessage(succ);
	}
}


