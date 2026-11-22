var resumepage = 1;
var miniRefresh = new MiniRefresh({
    container: '#minirefresh',
    down: {
        callback: function() {
            // 下拉事件
			setTimeout(function(){
				miniRefresh.endDownLoading();
				window.location.reload();
			},1000);
        }
    },
    up: {
        callback: function() {
            // 上拉事件
			fetchData();
        }
    }
});
function fetchData() {
	resumepage++;
	var searchurl = document.getElementById('searchurl').value;
	$.post(wapurl + 'index.php?c=resume&a=ajaxLoad', {searchurl: searchurl,page: resumepage}, function(res){
		if(res){
			var html = '';
			var data = eval('(' + res + ')');
			if(data.list.length > 0){
				data.list.forEach(function(val){
					html += '<div class="yun_newedition_resumelist">';
					html += '<a href="' + val.wapurl + '">';
					html += '<div class="yun_newedition_resumepic">';
					html += '<img src="' + val.photo + '" />';
					if(val.sex==1){
						var sexclass = 'user_tj_xb';
					}else{
						var sexclass = 'user_tj_xg';
					}
					html += '<span class="' + sexclass + '"></span></div>';
					html += '<div class="yun_newedition_resume_wantjob">';
					html += '<span class="yun_newedition_resume_wantjob_n">' + val.name + '</span></div>';
					html += '<div class="yun_newedition_resume_name">' + val.name_n;
					html += '<span class="user_i_n">';
					if(val.sex_n){
						html += val.sex_n + ',';
					}
					if(val.age > 0){
						html += val.age;
					}else{
						html += '保密';
					}
					html += '</span>';
					if(val.rec_resume == 1){
						html += '<span class="usertj"> 推荐</span>';
					}
					html += '</div>';
					html += '<div class="yun_newedition_userinfo">';
					if(val.exp_n){
						html +=  val.exp_n + '经验';
					}
					if(val.edu_n){
						html +=  '<i class="yun_newedition_jobline"></i>' + val.edu_n + '学历';
					}
					html += '</div>';
					if(val.work_content || val.edu_content){
						html += '<div class="user_undergo_box">';
						if(val.edu_content){
							html += '<div class="user_undergo user_undergo_a">' + val.edu_content + '</div>';
						}
						if(val.work_content){
							html += '<div class="user_undergo user_undergo_b">' + val.work_content + '</div>';
						}
						html += '</div>';
					}
					if(val.tag_arr){
						html += '<div class="yun_tjresumebox">';
						val.tag_arr.forEach(function(tval){
							html += '<span class="yun_tjresume">' + tval + '</span>';
						});
						html += '</div>';
					}
					html += '<span class="yun_newedition_resume_time">' + val.lastupdate_n + '</span>';
					if(data.lookResumeIds){
						if(data.lookResumeIds.indexOf(val.id)!=-1){
							html += '<div class="yun_ljl">. 浏览过的简历</div>';
						}
					}
					
					html += '</a></div>';
				});
				miniRefresh.endUpLoading(false);
			}else{
				miniRefresh.endUpLoading(true);
			}
		}
		document.getElementById('listdiv').insertAdjacentHTML('beforeend', html);
	});
}