// 头部导航栏保存
function headSave(type){
	if(type == 'info'){
		saveInfo();
	}else if(type == 'addexpect'){
		saveExpect();
	}
}
function saveInfo(){
	var field = getFormValue('infoForm');
	
	if(field.name == '') {
		return showToast('请输入姓名！');
	}
	if(field.sex == '') {
		return showToast('请选择性别！');
	}
	if(field.birthday == '') {
		return showToast('请选择出生年月！');
	}
	if(field.edu == '') {
		return showToast('请选择最高学历！');
	}
	if(field.exp == '') {
		return showToast('请选择工作经验！');
	}
	if(field.living == '') {
		return showToast('请输入现居住地！');
	}
	if(field.telphone == '') {
		return showToast('请输入手机！');
	}
	if(field.email != "" && !check_email(field.email)) {
		return showToast('邮箱格式错误！');
	}
	showLoading();
	$.post("index.php?c=saveInfo", field, function(res) {
		hideLoading();
		if(res.url) {
			showToast(res.msg, 2, function() {
				window.location.href = res.url;
			});
		} else {
			showToast(res.msg);
		}
	}, 'json');
}