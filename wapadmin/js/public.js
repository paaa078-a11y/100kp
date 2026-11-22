// 获取表单提交值
function getFormValue(formid) {
	var itemForm = $("#" + formid).eq(0);

	var nameIndex = {}, //数组 name 索引
		field = {},
		fieldElem = itemForm.find('input,select,textarea') //获取所有表单域
	
	fieldElem.each(function(_, item) {
		var init_name; //初始 name

		item.name = (item.name || '').replace(/^\s*|\s*&/, '');
		if (!item.name) return;
		//用于支持数组 name
		if (/^.*\[\]$/.test(item.name)) {
			var key = item.name.match(/^(.*)\[\]$/g)[0];
			nameIndex[key] = nameIndex[key] | 0;
			init_name = item.name.replace(/^(.*)\[\]$/, '$1[' + (nameIndex[key]++) + ']');
		}

		if (/^checkbox|radio$/.test(item.type) && !item.checked) return; //复选框和单选框未选中，不记录字段     
		field[init_name || item.name] = item.value;
	});
	
	return field;
}

/**
 * @desc 邮箱格式验证
 */
function check_email(strEmail) {
	var emailReg = /^([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9\-]+@([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
	if (emailReg.test(strEmail))
		return true;
	else
		return false;
}
/**
 * @desc 手机号码验证
 */
function isjsMobile(obj){
	var reg= /^[1][3456789]\d{9}$/;   
    if (obj.length != 11) return false;
    else if (!reg.test(obj)) return false;
    else if (isNaN(obj)) return false;
    else return true;
}
/**
 * @desc 电话验证
 */
function isjsTell(str) {
	var result = str.match(/^[0-9-]+?$/);
	if (result == null) return false;
    return true;
}

function navigateTo(url){
	window.location.href = url;
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
			location.href = wapurl;
		}
	}else{ //未知的浏览器
		window.history.go( -1 );
	}
}
var jl_flag;
// 节流函数：在一定时间内，只能触发一次
function throttle(func, wait = 1000){
	if (!jl_flag) {
		jl_flag = true;
		// 如果是立即执行，则在wait毫秒内开始时执行
		typeof func === 'function' && func();
		timer = setTimeout(() => {
			jl_flag = false;
		}, wait);
	}

}

var fdtimeout = null;
// 防抖原理：一定时间内，只有最后一次操作，再过wait毫秒后才执行函数
function debounce(func, wait = 500) {
	// 清除定时器
	if (fdtimeout !== null) clearTimeout(fdtimeout);
	fdtimeout = setTimeout(function() {
		typeof func === 'function' && func();
	}, wait);
}
/****
* name:localStorage的key
* data:localStorage的Value
* expire:localStorage的过期时间,默认是1天后过期
****/
//设置缓存
function localStorageSet(name, data, expire = 24){
	expire = new Date().getTime() + expire * 60 * 60 * 1000;
	var obj = {
			data,
			expire
		};
	window.localStorage.setItem(name, JSON.stringify(obj));
}
//读取缓存
function localStorageGet(name){
	var storage = window.localStorage.getItem(name);
	var result = null;
	if (storage) {
		var obj = JSON.parse(storage);
		var time = new Date().getTime();
		if (time < obj.expire) {
			result = obj.data;
		} else {
			 window.localStorage.removeItem(name);
		}
	}
	return result;
}
// 判断arr是否为一个数组，返回一个bool值
function isArray (arr) {
    return Object.prototype.toString.call(arr) === '[object Array]';
}
// 深度克隆
function deepClone (obj) {
	// 对常见的“非”值，直接返回原来值
	if([null, undefined, NaN, false].includes(obj)) return obj;
    if(typeof obj !== "object" && typeof obj !== 'function') {
		//原始类型直接返回
        return obj;
    }
    var o = isArray(obj) ? [] : {};
    for(let i in obj) {
        if(obj.hasOwnProperty(i)){
            o[i] = typeof obj[i] === "object" ? deepClone(obj[i]) : obj[i];
        }
    }
    return o;
}
/**
 * @desc 
 * @param imgObj
 * @param imgSrc
 * @param maxErrorNum
 * @returns
 */
function showImgDelay(imgObj,imgSrc,maxErrorNum){  
	if(maxErrorNum>0){
		imgObj.onerror=function(){
			
			showImgDelay(imgObj,imgSrc,maxErrorNum-1);
		};
		
		setTimeout(function(){
			imgObj.src	=	imgSrc;
		},500);
		
		maxErrorNum	=	parseInt(maxErrorNum) - parseInt(1);
    }
}