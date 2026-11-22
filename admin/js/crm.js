function keySelect(value, type, name){
	$("#"+type+"_name").html(name);
	$("#"+type).val(value);
	$("#"+type+"_n").hide();
}

var weburl = "{yun:}$config.sy_weburl{/yun}";

// 客户等级和客户状态统一选中处理
function checkCrmTs(crmstatus, crmtype)
{
	// 清空选中项
	$("#public_crm_status").find("option:selected").prop("selected", false);
	$("#public_crm_type").find("option:selected").prop("selected", false);
	$("#public_crm_status option[value='']").attr("selected", true);
	$("#public_crm_type option[value='']").attr("selected", true);

	if (crmstatus && crmstatus > 0) {
		$("#public_crm_status option[value='" + crmstatus + "']").attr("selected", true);
	}
	if (crmtype && crmtype > 0) {
		$("#public_crm_type option[value='" + crmtype + "']").attr("selected", true);
	}
}

$(document).ready(function(){
	
	$(".CrmnewFollow").click(function(){
		
		var uid	= $(this).attr('data-uid');
		var crmstatus = $(this).attr('data-crm_status');
		var crmtype = $(this).attr('data-crm_type');
		if (uid > 0) {
			checkCrmTs(crmstatus, crmtype);

			$('#public_link').html('<option value="">请选择联系人员</option>');
			var param = {};
			param.uid = uid;
			param.pytoken = $('#pytoken').val();
			$.post('index.php?m=crm_my_customer&c=getCrmLink', param, function (data) {
				var crmLink = eval('(' + data + ')');
				if (crmLink.length > 0) {
					crmLink.forEach((item, index) => {
						$('#public_link').append(new Option(item.link_man+" - "+item.link_mobile, item.link_man+"-"+item.link_mobile));
					});
					$("#public_link option:eq(1)").attr("selected", true);
				}
				form.render('select');
			});
		}else{
			$('#newLinkA').hide();
		}
		$("#com_uid").val(uid);
		var name= $(this).attr('data-name');
		if(name){
			
			$("#fcomname").val(name);
			$("#fcomname").attr("disabled", true);
		}
		var taskid=	$(this).attr('data-taskid');
		if(taskid){
			$("#crm_task_id").val(taskid);
		}

		var lasturl =  $(this).attr('data-lasturl');
		if (lasturl !=''){
			$('#lasturl').val(lasturl);
		}
		
		$("#newwindow").val('1');
		$('.layui-anim_all').html('');
		$.layer({
			type : 1,
			title :'联系跟进', 
			closeBtn : [0 , true],
			border : [10 , 0.3 , '#000', true],
			area : ['880px','auto'],
			offset: ['168px', ''],
			page : {dom :"#crmNewConcern"}
		});
	});
	
	$(".CrmnewTask").click(function(){
		var uid	= $(this).attr('data-uid');
		var name= $(this).attr('data-name');
		var auid= $("#auid").val();
		var info= $(this).attr('data-info');
		if(name == ''){
			parent.layer.msg("请先完善客户基本信息内容！", 2, 8);
			return false;
		}

		if(uid && name){
			$("#com_uid").val(uid);
			$("#tcomname").val(name);
			$("#tcomname").attr("disabled", true);
		}
		
		var tid	= $(this).attr('data-id');

		if(tid == '22'){
			$("#taskTimeshow").show();
		}
		
		if(info == '1'){
			$("#info").val('1');
		}
		
		$('.layui-anim_all').html('');
		$.layer({
			type : 1,
			title :'新建任务', 
			closeBtn : [0 , true],
			border : [10 , 0.3 , '#000', true],
			area : ['450px','auto'],
			offset: ['168px', ''],
			page : {dom :"#crmNewTask"}
		});
		
		layui.use([ 'form', 'laydate' ], function() {
			var form 	= layui.form, 
				$ 		= layui.$;
 			if(tid){
				$("#taskType").val(tid);
				if(tid=='22'){
					
					$("#taskHuid").attr("disabled","disabled"); 
					$("#taskHuid").val(auid); 
					$("#handle").hide();
					$("#taskTimeshow").attr("style","display:block;");
				}else{
					$("#handle").show();
					$("#taskHuid").attr("disabled",false);
					$("#taskTimeshow").attr("style","display:none;");
				}
				form.render('select');
			}
 			
		});
	});
	
	$("#newDeal").click(function(){
		
		var uid	=	$(this).attr('data-uid');
		var name=	$(this).attr('data-name');
		var info=	$(this).attr('data-info');
		if(uid && name){
			$("#com_uid").val(uid);
			$("#comname").val(name);
			$("#comname").attr("disabled", true);
		}
		
		$("#newwindow").val('1');
		if(info == '1'){
			$("#newwindow").val('0');
		}
		$('.layui-anim_all').html('');
		
		$.layer({
			type : 1,
			title :'录订单', 
			closeBtn : [0 , true],
			border : [10 , 0.3 , '#000', true],
			area : ['450px','auto'],
			offset: ['168px', ''],
			page : {dom :"#crmDeal"}
		});
	});
	
	$("#newWorkLog").click(function(){
		$("#logtitle").val('');
		$("#logcontent").val('')
		$.layer({
			type : 1,
			title :'新建工作日志', 
			closeBtn : [0 , true],
			border : [10 , 0.3 , '#000', true],
			area : ['650px','450px'],
			offset: ['168px', ''],
			page : {dom :"#crmworklog"}
		});
	});
	
	$("#newOut").click(function(){
		
		var uid			=	$(this).attr('data-uid');
		var name		=	$(this).attr('data-name');
		var newWindow	=	$(this).attr('data-new');
		$("#com_uid").val(uid);
		$("#outComName").val(name);
		if(newWindow){
			$("#newwindow").val(newWindow);
		}
		$.layer({
			type : 1,
			title :'外出申请', 
			closeBtn : [0 , true],
			border : [10 , 0.3 , '#000', true],
			area : ['500px','auto'],
			offset: ['200px', ''],
			page : {dom :"#crmOut"}
		});
	});
});

//	公共弹出框，保存跟进记录
function addFollowByPublic(){

	var param 	=	{};
	param.uid 	= 	$("#com_uid").val();
	param.type 	= 	$("#public_follow_way option:selected").val();
	param.crm_status = $("#public_crm_status option:selected").val();
	param.crm_type = $("#public_crm_type option:selected").val();

	var ftime	=	$("#public_ftime").val();
	if(ftime=='' || ftime==undefined || !ftime){
		parent.layer.msg('请填写跟进时间！',2,8);
		return false;
	}else{
		param.ftime = ftime;
	}
	
	var public_link = $('#public_link').val();
	var linkArr = public_link.split('-');
	var newLinkMan =linkArr[0];
	var newLinkTel =linkArr[1];

	if (newLinkMan == '' || newLinkTel == ''){

		parent.layer.msg('请选择客户联系人员！',2,8);
		return false;
	}else  {

		param.link_man = newLinkMan;
		param.link_tel = newLinkTel;
	}

	param.remark=	publicFollowContent.getContent();

	var follow	=	$("#public_next_check").is(":checked") ? 1: 0;
	param.follow = follow;
	if(follow == 1){
		param.next_time	=	$("#public_next_time").val();
	}

	var logid	=	$("#call_log_id").val();
	if (logid!= ''){
		param.call_log_id = logid;
	}
	param.pytoken	=	$('#pytoken').val();

	var lasturl = 	$('#lasturl').val();

	loadlayer();
	$.post('index.php?m=crm_my_customer&c=addFollow', param, function(data){
		parent.layer.closeAll('loading');
		var data = eval('('+data+')');
		if(data.errcode == '9'){
			parent.layer.msg(data.msg, 2, 9, function(){
				if (lasturl!=''){
					location.href = lasturl;
				}else{
					location.href = 'index.php?m=crm_my_customer';
				}
			});
		}else if (data.errcode == '8'){
			parent.layer.msg(data.msg, 2, 8);
			return false;
		}
	});
}

function OpenContact(uid,url){
	var pytoken = $('#pytoken').val();
	$.post(url,{pytoken:pytoken,uid:uid},function(data){
		var data=eval('('+data+')');
		$('#comLink_name').val(data.name);
		$('#comLink_ratingname').val(data.ratingname);
		$('#comLink_vipetime').val(data.ratingtime);
		$('#comLink_city').val(data.cityname);
		$('#comLink_linkman').val(data.linkman);
		$('#comLink_linktel').val(data.moblie);
		
		$.layer({
			type : 1,
			title :'客户信息', 
			closeBtn : [0 , true],
			border : [10 , 0.3 , '#000', true],
			area : ['auto','380px'],
			offset: ['168px', ''],
			page : {dom :"#comLinkInfo"}
		});
	});
}

function CompleteDetail(id,url){
	var pytoken = $('#pytoken').val();
	$.post(url,{pytoken:pytoken,id:id},function(data){
		$('#reason').val(data);
		$('#taskreasonsubmit').hide();
		
		$.layer({
			type : 1,
			title :'任务未完成原因', 
			closeBtn : [0 , true],
			border : [10 , 0.3 , '#000', true],
			area : ['450px','250px'],
			offset: ['168px', ''],
			page : {dom :"#taskstatus"}
		});
	});
}
 
function crmworklog(){
	if(!$("#logtitle").val()){
       layer.msg('请填写日志标题',2,8);return false;
	}
	if(!$("#logcontent").val()){
       layer.msg('请填写日志内容',2,8);return false;
	}
}

function showWorkLog(id,url) {
	var pytoken = $('#pytoken').val();
	$.post(url,{pytoken:pytoken,id:id},function(data){
		var data=eval('('+data+')');
		$('#logtitleshow').html(data.title);

		$('#logcontentshow').html(data.content);
	});
	$.layer({
		type : 1,
		title :'工作日志详情', 
		closeBtn : [0 , true],
		border : [10 , 0.3 , '#000', true],
		area : ['450px','300px'],
		offset: ['168px', ''],
		page : {dom :"#worklogshow"}
	});
}

$(document).ready(function(){

	$("#Deliver").click(function () {
		var uids = "";
		$(".check_all:checked").each(function () {
			if (uids == "") {
				uids = $(this).val();
			} else {
				uids = uids + "," + $(this).val();
			}
		});
		if (uids == "") {
			parent.layer.msg("请选择要转交的客户！", 2, 8);
			return false;
		} else {
			$("#com_uid").val(uids);
		}
		$.layer({
			type: 1,
			title: '转交客户',
			closeBtn: [0, true],
			border: [10, 0.3, '#000', true],
			area: ['450px', 'auto'],
			offset: ['200px', ''],
			page: {dom: "#crmDeliver"}
		});
	});
	
	$("#Giveup").click(function(){
		var uid	=	$(this).attr('data-uid');
		if(uid){
			$("#com_uid").val(uid);
		}else{
			var uids = "";
			$(".check_all:checked").each(function() { 
				if (uids == "") {
					uids = $(this).val();
				} else {
					uids = uids + "," + $(this).val();
				}
			});
			if (uids == "") {
				parent.layer.msg("请选择要放弃的客户！", 2, 8);
				return false;
			} else {
				$("#com_uid").val(uids);
			}
		}
		$.layer({
			type : 1,
			title :'放弃客户', 
			closeBtn : [0 , true],
			border : [10 , 0.3 , '#000', true],
			area : ['450px','auto'],
			offset: ['200px', ''],
			page : {dom :"#crmGiveup"}
		});
	});
	
	$(".CrmnewReceive").click(function(){

		var uid		=	$(this).attr('data-uid');
		if(uid){
			var uids = uid;
		}else{
			var uids = "";
			$(".check_all:checked").each(function() { 
				if (uids == "") {
					uids = $(this).val();
				} else {
					uids = uids + "," + $(this).val();
				}
			});
			if (uids == "") {
				parent.layer.msg("请选择要领取的客户！", 2, 8);
				return false;
			} 
		}
		var pytoken	=	$('#pytoken').val();
		loadlayer();
		$.post('index.php?m=crm_customer&c=claimCom', {uids: uids , pytoken:pytoken}, function(data){
			parent.layer.closeAll('loading');
			var data = eval('('+data+')');
			if(data.errcode == '9'){
				parent.layer.msg(data.msg, 2, 9, function(){
					location.reload();
				});
			}else if (data.errcode == '8'){
				parent.layer.msg(data.msg, 2, 8);
				return false;
			}
		});
	});

	$(".clueCustomer").click(function(){

		var uid		=	$(this).attr('data-uid');
		if(uid){
			var uids = uid;
		}else{
			var uids = "";
			$(".check_all:checked").each(function() {
				if (uids == "") {
					uids = $(this).val();
				} else {
					uids = uids + "," + $(this).val();
				}
			});
			if (uids == "") {
				parent.layer.msg("请选择要转换的线索！", 2, 8);
				return false;
			}
		}
		var pytoken	=	$('#pytoken').val();
		loadlayer();
		$.post('index.php?m=crm_customer&c=clueCustomer', {uids: uids , pytoken:pytoken}, function(data){
			parent.layer.closeAll('loading');
			var data = eval('('+data+')');
			if(data.errcode == '9'){
				parent.layer.msg(data.msg, 2, 9, function(){
					location.reload();
				});
			}else if (data.errcode == '8'){
				parent.layer.msg(data.msg, 2, 8);
				return false;
			}
		});
	});

	$(".clueMyCustomer").click(function(){

		var from = $(this).attr('data-from');

		var uids = "";
		$(".check_all:checked").each(function() {
			if (uids == "") {
				uids = $(this).val();
			} else {
				uids = uids + "," + $(this).val();
			}
		});
		if (uids == "") {
			parent.layer.msg("请选择要转换的线索！", 2, 8);
			return false;
		}

		var pytoken	=	$('#pytoken').val();
		loadlayer();
		$.post('index.php?m=crm_my_customer&c=clueCustomer', {uids: uids , from: from, pytoken:pytoken}, function(data){
			parent.layer.closeAll('loading');
			var data = eval('('+data+')');
			if(data.errcode == '9'){
				parent.layer.msg(data.msg, 2, 9, function(){
					location.href = data.url;
				});
			}else if (data.errcode == '8'){
				parent.layer.msg(data.msg, 2, 8);
				return false;
			}
		});
	});
	
	$("#UserStatus").click(function(){


		var uids = "";
		$(".check_all:checked").each(function() {
			if (uids == "") {
				uids = $(this).val();
			} else {
				uids = uids + "," + $(this).val();
			}
		});
		if (uids == "") {
			parent.layer.msg("请选择要修改状态的客户！", 2, 8);
			return false;
		} else {
			$("#com_uid").val(uids);
			layui.use(['form'], function() {
				var f = layui.form;
				f.render();
			});
		}
		$("#isSt").val('status');
		$("#select_CrmStatus").show();
		$("#select_CrmType").hide();
		$("#st_Remark").val($(this).attr('data-remark'));
		$.layer({
			type : 1,
			title :'客户状态', 
			closeBtn : [0 , true],
			border : [10 , 0.3 , '#000', true],
			area : ['450px','auto'],
			offset: ['200px', ''],
			page : {dom :"#crmStatusType"}
		});
	});
	
	$("#LevelType").click(function(){
		var uid		=	$(this).attr('data-uid');
		if(uid){
			var type	=	$(this).attr('data-type');
			$("#com_uid").val(uid);
			layui.use(['form'], function() {
		        var f = layui.form;
		        f.val('formCrmStatusType',{
		        	"level": type
		        })
		        f.render();
		    });
		}else{
			var uids = "";
			$(".check_all:checked").each(function() { 
				if (uids == "") {
					uids = $(this).val();
				} else {
					uids = uids + "," + $(this).val();
				}
			});
			if (uids == "") {
				parent.layer.msg("请选择要修改等级的客户！", 2, 8);
				return false;
			} else {
				$("#com_uid").val(uids);
				layui.use(['form'], function() {
			        var f = layui.form;
			        f.render();
			    });
			}
		}
		$("#isSt").val('type');
		$("#select_CrmStatus").hide();
		$("#select_CrmType").show();
		$("#st_Remark").val($(this).attr('data-remark'));
		$.layer({
			type : 1,
			title :'客户等级', 
			closeBtn : [0 , true],
			border : [10 , 0.3 , '#000', true],
			area : ['450px','auto'],
			offset: ['200px', ''],
			page : {dom :"#crmStatusType"}
		});
	});
	
    $("#crmRemarkCom").click(function(){
		
		var remark	=	$("#crm_remark_com").val();
		var uid		=	$("#crmComid").val();
		if(remark == ''){
			layer.msg('请填写备注信息！');
			return false;
		}
		var pytoken	=	$('#pytoken').val();
		loadlayer();
		$.post('index.php?m=crm_my_customer&c=remarkCom', {uid: uid, crm_remark:remark, pytoken:pytoken}, function(data){
			parent.layer.closeAll('loading');
			var data = eval('('+data+')');
			if(data.errcode == '9'){
				parent.layer.msg(data.msg, 2, 9, function(){
					location.href = 'index.php?m=crm_my_customer&c=com&id='+uid;
				});
			}else if (data.errcode == '8'){
				parent.layer.msg(data.msg, 2, 8);
				return false;
			}
		});
	});

	$("#newOrder").click(function(){
		
		var id		=	$("#orderId").val();
		var uid		=	$("#com_uid").val();
		var crm_uid	=	$("#crm_uid").val();

		if(uid == ''){
			parent.layer.msg('请填写客户名称！', 2, 8);
			return false;
		}
		var order_type	=	$("#order_type option:selected").val();
		if(order_type == ''){
			parent.layer.msg('请选择支付方式！', 2, 8);
			return false;
		}
		var type	=	$("#orderType option:selected").val();
		if(type == ''){
			parent.layer.msg('请选择订单类型！', 2, 8);
			return false;
		}else if (type == 1) {
			var rating = $("#rid_val option:selected").val();
			if (rating == '') {
				parent.layer.msg('请选择会员套餐！', 2, 8);
				return false;
			}
			var deal_model = $("input[name=deal_model]:checked").val();
		}
		var price		=	$("#order_price").val();
		var remark		=	$("#order_remark").val();
		var pytoken		=	$('#pytoken').val();
		var picurls = []
		$(".addpicurls").each(function(){
            picurls.push($(this).val())
		});
		loadlayer();
		$.post('index.php?m=crm_index&c=addDeal', {id: id, uid: uid, crm_uid: crm_uid, order_price: price, order_type: order_type, type: type, rating: rating, deal_model: deal_model, order_remark: remark, htpics: picurls, pytoken:pytoken}, function(data){
			parent.layer.closeAll('loading');
			var data = eval('('+data+')');
			if(data.errcode == '9'){
				parent.layer.msg(data.msg, 2, 9, function(){
					layer.closeAll();
					iframfunct('index.php?m=crm_my_customer&c=com&id='+uid, '80%');

				});
			}else if (data.errcode == '8'){
				parent.layer.msg(data.msg, 2, 8);
				return false;
			}
		});
	});

	$("#crmTask").click(function(){

		var type	=	$("#taskType option:selected").val();
		if(type == ''){
			parent.layer.msg('请选择任务类型！', 2, 8);return false;
		}
		var comid	=	$("#com_uid").val();
		if(comid == ''){
			parent.layer.msg('客户数据错误，请重新填写客户名称！', 2, 8);return false;
		}
		var taskHuid	=	$("#taskHuid option:selected").val();
		if(taskHuid == '' ){
			parent.layer.msg('请选择执行人员！', 2, 8);return false;
		}
		var now		=	Date.parse(new Date()) / 1000;
		var stime	=	$('#taskTime').val();
		if(type=='22' && stime){
			stime	=	Date.parse(new Date(stime)) / 1000;
			if(stime < now){
				parent.layer.msg('跟进任务时间需在当前时间之后，请重新填写',2,8);
				return false;
			}
		}
		var remark	=	$("#taskRemark").val();
		if(remark == ''){
			parent.layer.msg('请简要地填写任务描述内容', 2, 8);return false;
		}
		var info	=	$("#info").val();
		var pytoken	=	$('#pytoken').val();
		loadlayer();
		$.post('index.php?m=crm_waitingtask&c=add', {comid: comid, type: type, content: remark, stime: stime,taskHuid:taskHuid, pytoken:pytoken}, function(data){
			parent.layer.closeAll('loading');
			var data = eval('('+data+')');
			if(data.errcode == '9'){
				parent.layer.msg(data.msg, 2, 9, function(){
					if(info == '1'){
						location.href = 'index.php?m=crm_my_customer&c=com&id='+comid;
					}else{
						location.href = 'index.php?m=crm_waitingtask';
					}
				});
			}else if (data.errcode == '8'){
				parent.layer.msg(data.msg, 2, 8);
				return false;
			}
		});
	});

    $("#crmComInfo").click(function(){
		
		var uid			=	$("#crmComid").val();
		var linkman		=	$("#linkman").val();
		var linkjob		=	$("#linkjob").val();
		var linktel		=	$("#linktel").val();
		var linkphone	=	$("#linkphone").val();
		var linkmail	=	$("#linkmail").val();
		if(linktel =='' ){
			layer.msg('请填写联系手机！');
			return false;
		}else if(!isjsMobile(linktel)){
			layer.msg('联系手机格式错误！');
			return false;
		}
		if(linkphone  && !isjsTell(linkphone)){
			layer.msg('固定电话格式错误！');
			return false;
		}
		if(linkmail  && !check_email(linkmail)){
			layer.msg('联系邮箱格式错误！');
			return false;
		}
		var pytoken	=	$('#pytoken').val();
		loadlayer();
		$.post('index.php?m=crm_my_customer&c=upComLink', {uid: uid,linkman:linkman, linkjob:linkjob, linktel:linktel, linkphone:linkphone, linkmail:linkmail, pytoken:pytoken}, function(data){
			parent.layer.closeAll('loading');
			var data = eval('('+data+')');
			if(data.errcode == '9'){
				parent.layer.msg(data.msg, 2, 9, function(){
					location.href = 'index.php?m=crm_my_customer&c=com&id='+uid;
				});
			}else if (data.errcode == '8'){
				parent.layer.msg(data.msg, 2, 8);
				return false;
			}
		});
	});
})

function crmDeliver(){
	
	var crmuser	= 	$("#crmuser  option:selected").val();
	if(crmuser == ''){
		layer.msg('请选择需要转交的客户经理！');
		return false;
	}else{
		var crmArr 	= 	crmuser.split('-');
		var crm_uid	=	crmArr[0];
		var crm_name= 	crmArr[1];
	}
	var uid		=	$("#com_uid").val();
	if(uid == ''){
		layer.msg('请选择需要转交的客户！');
		return false;
	}
	var remark	=	$("#deliverRemark").val();	
	var pytoken	=	$("#pytoken").val();
	loadlayer();
	$.post('index.php?m=crm_my_customer&c=deliver', {crm_uid: crm_uid, crm_name: crm_name, uid: uid, remark: remark, pytoken:pytoken}, function(data){
		parent.layer.closeAll('loading');
		var data = eval('('+data+')');
		if(data.errcode == '9'){
			parent.layer.msg(data.msg, 2, 9, function(){
				location.reload();
			});
		}else if (data.errcode == '8'){
			parent.layer.msg(data.msg, 2, 8);
			return false;
		}
	});
}

function crmGiveup(){
	
	var uid		=	$("#com_uid").val();
	if(uid == ''){
		layer.msg('请选择需要放弃的客户！');
		return false;
	}
	var remark	=	$("#giveupRemark").val();
	if(remark == ''){
		layer.msg('请填写放弃客户的说明备注！');
		return false;
	}
	var pytoken	=	$("#pytoken").val();
	loadlayer();
	$.post('index.php?m=crm_my_customer&c=giveUp', {uid: uid, remark: remark, pytoken:pytoken}, function(data){
		parent.layer.closeAll('loading');
		var data = eval('('+data+')');
		if(data.errcode == '9'){
			parent.layer.msg(data.msg, 2, 9, function(){
				location.reload();
			});
		}else if (data.errcode == '8'){
			parent.layer.msg(data.msg, 2, 8);
			return false;
		}
	});
}

function crmStatusType(){
	
	var uid		=	$("#com_uid").val();
	if(uid == ''){
		layer.msg('请选择需要修改的客户！');
		return false;
	}
	var isSt	=	$("#isSt").val();
	if(isSt == 'status'){
		var status	=	$('input[name="status"]:checked').val();
		if(status == '' || status == undefined){
			layer.msg('请选择需要客户状态！');
			return false;
		}else{
			var status 	= 	status.split('-');
			var crm_st	=	status[0];
			var crm_st_n= 	status[1];
		}
	}else if(isSt == 'type'){
		var type	=	$('input[name="level"]:checked').val();
		if(type == ''){
			layer.msg('请选择需要客户等级！');
			return false;
		}else{
			var type 	= 	type.split('-');
			var crm_st	=	type[0];
			var crm_st_n= 	type[1];
		}
	}
	var remark	=	$("#st_Remark").val();
	var pytoken	=	$("#pytoken").val();
	loadlayer();
	$.post('index.php?m=crm_my_customer&c=upStatusType', {uid: uid, st: crm_st, st_n:crm_st_n, isSt: isSt, remark: remark, pytoken:pytoken}, function(data){
		
		parent.layer.closeAll('loading');
		var data = eval('('+data+')');
		if(data.errcode == '9'){
			parent.layer.msg(data.msg, 2, 9, function(){
				location.reload();
			});
		}else if (data.errcode == '8'){
			parent.layer.msg(data.msg, 2, 8);
			return false;
		}
	});
}

function crmOut(){
	var reason	= 	$("#reason").val();
	var stime	=	$("#outStime").val();
	var etime	=	$("#outEtime").val();
	var remark	=	$("#outRemark").val();
	var uid		=	$("#com_uid").val();
	var name	=	$("#outComName").val();
	if(!uid && !name){
       layer.msg('请填写客户名称',2,8);return false;
	}
	if(!reason){
       layer.msg('请选择外出原因',2,8);return false;
	}
	if(!stime){
       layer.msg('请选择外出时间',2,8);return false;
	}
	var now		=	Date.parse(new Date()) / 1000;
	if(stime){
		
		stime	=	Date.parse(new Date(stime)) / 1000;
		if(stime < now){
			layer.msg('外出时间必须大于现在时间，请重新设置',2,8);
			return false;
		}
		etime	=	Date.parse(new Date(etime)) / 1000;
		if(stime > etime){
			layer.msg('返回时间必须大于外出时间，请重新设置',2,8);
			return false;
		}
	}
	var newWindow = $("#newwindow").val();
	var pytoken	=	$("#pytoken").val();
	loadlayer();
	$.post('index.php?m=crm_out&c=add', {reason:reason, uid: uid, name: name, stime: stime, etime: etime, remark: remark, pytoken: pytoken}, function(data){
		parent.layer.closeAll('loading');
		var data = eval('('+data+')');
		if(data.errcode == '9'){
			parent.layer.msg(data.msg, 2, 9, function(){
				if(newWindow == '1'){
					location.href = 'index.php?m=crm_out';
				}else{
					location.href = 'index.php?m=crm_my_customer&c=com&id='+uid;
				}
			});
		}else if (data.errcode == '8'){
			parent.layer.msg(data.msg, 2, 8);
			return false;
		}
	});
}