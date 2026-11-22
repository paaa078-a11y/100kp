var uniReady = false;
document.addEventListener('UniAppJSBridgeReady', function() {
	if(comid.trim() !=''){
		uniReady = true;
	}
	if(isApp){
		// App
		$("#imgDiv").css('-webkit-touch-callout', 'none');
			uni.postMessage({
			data: {
				type: 'loadReady'
			}
		});
	}
});
var timer = null;
document.getElementById('showImg').addEventListener('touchstart', function(){
	if(uniReady && isApp){
		timer = setTimeout(longTap, 700);
	}
});
document.getElementById('showImg').addEventListener('touchend', function(){
	if(uniReady && isApp){
		clearTimeout(timer);
	}
});
function longTap(){
	// IOS长屏幕，会导致下面的按钮要点击两次，按高度处理，增加bottom
	var clientHeight = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
	var viewWidth = window.screen.availWidth || window.screen.width;
	var posterHeight = (viewWidth * 1.7) > clientHeight ? '10px' : '50px';
	
	layer.open({
		content:'选择操作',
		btn: ['保存到手机', '取消'],
		skin: 'footer',
		style: 'bottom: ' + posterHeight,
		yes: function(){
			layer.closeAll();
			saveBtmap();
		}
	}); 
}
function saveBtmap(){
	var bitmap = new plus.nativeObj.Bitmap('haibao');
	var baseImg = document.getElementById('showImg').src;
	// 加载Base64编码格式图片到Bitmap对象  
	bitmap.loadBase64Data(baseImg, function(){  
		//console.log("加载Base64图片数据成功");
		var fileName = Date.now() + '.png';
		bitmap.save("_doc/" + fileName, {}, function(i) {
			//console.log('保存图片成功：' + JSON.stringify(i));
			plus.gallery.save(i.target, function(e) {
				//console.log('保存到相册成功：' + JSON.stringify(e));
				uni.postMessage({
					data: {
						type: 'saveSuccess'
					}
				})
			}, function(e) {
				//console.log('保存到相册失败：' + JSON.stringify(e));
				uni.postMessage({
					data: {
						type: 'saveFail',
						reason: e
					}
				})
			})
		}, function(e) {
			//console.log('保存图片失败：' + JSON.stringify(e));
		});
	}, function(e){  
		//console.log('加载Base64图片数据失败：'+JSON.stringify(e));  
	} );
}