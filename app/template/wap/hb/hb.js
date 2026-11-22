/* 选择职位样式改变 */
function ck(id){
	var isCss =	$('#jobid_'+id).hasClass('hb_jobchose_input_cur');
	if(isCss){
		$('#jobid_'+id).removeClass('hb_jobchose_input_cur');
		document.getElementById('job'+id).checked = false;
	}else{
		$('#jobid_'+id).addClass('hb_jobchose_input_cur');
		document.getElementById('job'+id).checked = true;
	}
}

/* 职位选择弹出框 */
function getHbJob(){
	$("#bg").css('display','block');
	$("#jobList").css('display','block');
}

function closeJob(){
	
	$("#bg").css('display','none');
	$("#jobList").css('display','none');
}

/* 职位选择检测 */
function checkJob(name){
	
	var chk_value 	= 	[];
	var i 			= 	0;
	
	$('input[name="' + name + '"]:checked').each(function() {
		chk_value.push($(this).val());
		i++;
	});
	// 按模板处理职位最大数量
	var jobmax = 5;
	if(hb == 3 || hb == 7 || hb == 8){
		jobmax = 6;
	}else if(hb == 4 || hb == 5 || hb == 10){
		jobmax = 4;
	}else if(hb == 6){
		jobmax = 3;
	}else if(hb == 9){
		jobmax = 2;
	}
	if(chk_value.length == 0) {
		$('#hbtip').html('至少选择1份职位数据！');
		$("#hbtip").fadeIn('slow',function(){
			setTimeout(function(){
				$("#hbtip").fadeOut('slow');
			},2000);
		});
		return false;
	} else if(chk_value.length > jobmax) {
		$('#hbtip').html('最多选择' + jobmax + '份职位数据！');
		$("#hbtip").fadeIn('slow',function(){
			setTimeout(function(){
				$("#hbtip").fadeOut('slow');
			},2000);
		});
		return false;
	} else {
		
		$("#jobList").css('display','none');
		$.ajax({
			type: 'POST',
			url: wapurl+'index.php?c=company&a=getHbJob',
			cache: false,
			dataType: 'json',
			data: {
				comid: comid,
				jobids: chk_value,
				submit: 1
			},
			success: function(res) {
				if(res.length>0) {
					var html = '';
					// 渲染职位列表，按模板区分
					for(var j=0;j<res.length;j++){
						if(hb == 2){
							html += '<div class="hb_jobbox2list"><div class="hb_jobbox2list_jobname">'+res[j].name+'</div><div class="hb_jobbox2list_p">薪资待遇：'+res[j].job_salary+'</div><div class="hb_jobbox2list_p">岗位要求：年龄'+res[j].job_age+'，'+res[j].job_edu+'学历，'+res[j].job_exp+'经验</div></div>';
						}else if(hb == 3){
							html += '<div class="hb_jobbox3list"><div class="hb_jobbox3name_s">'+res[j].name+'</div><div class="hb_jobbox3zz"><div class="hb_jobbox3xz_n">'+res[j].job_salary+'</div></div></div>';
						}else if(hb == 4){
							html +=	'<div class="hb_jobbox4list"><div class="hb_jobbox4list_jobname">'+res[j].name+'</div><div class="hb_jobbox4list_p">'+res[j].job_salary+'</div></div>';
						}else if(hb == 5){
							html +=	'<li>'+res[j].name+'<span class="hb_jobbox5list_xz">'+res[j].job_salary+'</span></li>';
						}else if(hb == 6){
							html +=	'<div class="hb_jobbox6list"><span class="hb_jobbox6name_s">'+res[j].name+'</span><div class="hb_jobbox6list_p">'+res[j].job_salary+'<span class="hb_jobbox6line">|</span>'+res[j].job_edu+'学历<span class="hb_jobbox6line">|</span>'+res[j].job_exp+'经验</div><div class="hb_jobbox6list_p"></div></div>';
						}else if(hb == 7){
							html +=	'<div class="hb_jobbox7list"><div class="hb_jobbox7name"> '+res[j].name+'</div><div class="hb_jobbox7xz">'+res[j].job_salary+'</div></div>';
						}else if(hb == 8){
							html += '<div class="hb_jobbox8list"><span class="hb_jobbox8name_s">'+res[j].name+'</span><div class="hb_jobbox8list_p">'+res[j].job_salary+'</div></div>';
						}else if(hb == 9){
							html += '<div class="hb_jobbox9name"><span class="hb_jobbox9name_s">'+res[j].name+'</span></div><div class="hb_jobbox9zz"> 岗位薪资：'+res[j].job_salary+'</div><div class="hb_jobbox9zz">岗位要求：年龄'+res[j].job_age+'，'+res[j].job_edu+'学历，'+res[j].job_exp+'经验。</div>';
						}else if(hb == 10){
							html += '<div class="hb_10_job"><div class="hb_10jobname">'+res[j].name+'</div><div class="hb_10gwzz"><span class="hb_10_xz">'+res[j].job_salary+'</span>';
							if(res[j].job_city_two){
								html += '<span class="hb_10_city">'+res[j].job_city_two+'</span>';
							}
							html += '<span class="hb_10_jy">'+res[j].job_exp+'</span></div></div>';
						}
					}
					$("#hb_joblist").html(html);

					toCanvas();
				}
			}
		});  
	}
}

/**
 * 生成海报图
 */
function toCanvas() {
	var w = $("#backurl").width();
	var h = $("#backurl").height();
	var scaleBy = getDpr();

	//要将 canvas 的宽高设置成容器宽高的 2 倍
	var canvas = document.createElement("canvas");	//创建canvas 对象
	canvas.width = w * scaleBy;
	canvas.height = h * scaleBy;
	canvas.style.width = w + "px";
	canvas.style.height = h + "px";
	var context = canvas.getContext("2d");
	
	//然后将画布缩放，将图像放大两倍画到画布上
	context.scale(scaleBy, scaleBy);
	html2canvas(document.querySelector("#backurl"), {
		background: "#fff",
		allowTaint: true,
		width: w,
		height: h,
		canvas: canvas,
		scale: scaleBy
	}).then(canvas => {
		context.mozImageSmoothingEnabled = false;
		context.webkitImageSmoothingEnabled = false;
		context.msImageSmoothingEnabled = false;
		context.imageSmoothingEnabled = false;
		var uimage = canvas.toDataURL("image/png", 1); // base64数据
		document.querySelector("#showImg").src = uimage;
		$("#imgDiv").show();
		$(".noSHow").hide();
		$("#bg").hide();
	});
}
/**
 * 生成海报图
 */
function toCanvas2(out) {
	var w = $("#backurl").width();
	var h = $("#backurl").height();
	var scaleBy = getDpr();

	//要将 canvas 的宽高设置成容器宽高的 2 倍
	var canvas = document.createElement("canvas");	//创建canvas 对象
	canvas.width = w * scaleBy;
	canvas.height = h * scaleBy;
	canvas.style.width = w + "px";
	canvas.style.height = h + "px";
	var context = canvas.getContext("2d");
	
	//然后将画布缩放，将图像放大两倍画到画布上
	context.scale(scaleBy, scaleBy);
	//console.log('scale' + getDpr());
	html2canvas(document.querySelector("#backurl"), {
		background: "#fff",
		allowTaint: true,
		width: w,
		height: h,
		canvas: canvas,
		scale: scaleBy
	}).then(canvas => {
		context.mozImageSmoothingEnabled = false;
		context.webkitImageSmoothingEnabled = false;
		context.msImageSmoothingEnabled = false;
		context.imageSmoothingEnabled = false;
		var url = canvas.toDataURL("image/png", 1); // base64数据

		document.querySelector("#showImg").src = url;
		$("#showImg").css('width', '260px');
		$("#showImg").css('height', '442px');
		$("#imgDiv").show();
		$(".noSHow").hide();

		if(parseInt(out) > 0){
			startDown(out);
		}
	});
}

function startDown(out){
	var img = document.getElementById('showImg'); // 获取要下载的图片
	var url = img.src;                            // 获取图片地址
	var a = document.createElement('a');          // 创建一个a节点插入的document
	var event = new MouseEvent('click')           // 模拟鼠标click点击事件
	a.download = 'whb'+ out;					  // 设置a节点的download属性值
	a.href = url;                                 // 将图片的src赋值给a节点的href
	a.dispatchEvent(event);
	window.close();
}
/**
 * 根据 window.devicePixelRatio 获取像素比
 */
function getDpr() {
	if (window.devicePixelRatio && window.devicePixelRatio > 1) {
		return window.devicePixelRatio;
	}
	return 1;
}
// 处理跨域问题，将背景图转成base64
//背景图
var bgt = document.getElementById('bgt');
if(bgt){
	var backImg = new Image();
	backImg.setAttribute("crossOrigin",'Anonymous');
	backImg.src = bgt.src;
	backImg.onload = function() {
		var backbase = getBase64Image(backImg); //调用函数并将其转为base64图片
		bgt.crossOrigin = 'Anonymous';
		bgt.src = backbase;
	};
}
// 二维码
var ewm = document.getElementById('comQrcode');
if(ewm){
	var comQrcode = new Image();
	comQrcode.setAttribute("crossOrigin",'Anonymous');
	comQrcode.src = ewm.src;;
	comQrcode.onload = function() {
		var combase = getBase64Image(comQrcode); //调用函数并将其转为base64图片
		ewm.crossOrigin = 'Anonymous';
		ewm.src = combase;
	};
}
function getBase64Image(img) {
	var canvas = document.createElement("canvas");
	canvas.width = img.width;
	canvas.height = img.height;
	var ctx = canvas.getContext("2d");
	ctx.drawImage(img, 0, 0, img.width, img.height);
	var ext = img.src.substring(img.src.lastIndexOf(".") + 1).toLowerCase();
	var dataURL = canvas.toDataURL("image/" + ext);
	return dataURL;
}