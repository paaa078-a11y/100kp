var jobpage = 1;
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
	jobpage++;
	var searchurl = document.getElementById('searchurl').value;
	$.post(wapurl + 'index.php?c=job&a=ajaxLoad', {searchurl: searchurl,page: jobpage}, function(res){
		if(res){
			var html = '';
			var data = eval('(' + res + ')');
			if(data.list.length > 0){
				data.list.forEach(function(val){
					html += '<div class="yun_newedition_job">';
					html += '<a href="' + val.wapurl + '" title="' + val.name + '">';
					html += '<div class="yun_newedition_jobcont">';
					html += '<div class="yun_newedition_jobname"><h3>' + val.name + '</h3></div>';
					html += '<span class="yun_newedition_jobxz">' + val.job_salary + '</span>';
					if(!val.urgent_n && !val.rec_n){
						html += '<span class="yun_newedition_jobtime">' + val.lastupdate_n + '</span>';
					}
					html += '<div class="yun_newedition_jobinfo"> <div class="yun_newedition_jobinfo_c">';
					if(val.job_city_one){
						html += val.job_city_one;
					}
					if(val.job_city_two){
						html += '-' + val.job_city_two;
					}
					if(val.job_exp){
						html += '<i class="yun_newedition_jobline"></i>' + val.job_exp + '经验';
					}
					if(val.job_edu){
						html += '<i class="yun_newedition_jobline"></i>' + val.job_edu + '学历';
					}
					html += '</div>';
					html += ' <div class="yun_index_jobemergency_tg" style="top:50px;">';
					if(val.urgent_n){
						html += '<span class="yun_index_jobemergency">急聘</span>';
					}
					if(val.rec_n){
						html += '<span class="yun_index_hot">推荐</span>';
					}
					html += '</div>';
					html += '<i class="job_todaynew">';
					if(val.newtime){
						html += '新职位';
					}
					if(data.lookJobIds){
						if(data.lookJobIds.indexOf(val.id)!=-1){
							html += '<span class="">. 浏览过</span>';
						}
					}
					html += '</i></div>';
					if(val.welfare_n){
						html += '<div class="yun_newedition_jobflbox">';
						val.welfare_n.forEach(function(wval){
							html += '<span class="yun_newedition_jobfl">' + wval + '</span>';
						});
						html += '</div>';
					}
					html += '</div>';
					html += '<div class="yun_newedition_comname">';
					html += '<div class="yun_joblogo"><img src="' + val.comlogo + '"></div>';
					html += val.com_name;
					if(val.hotlogo){
						html += '<img src=" ' + weburl + '/app/template/wap/images/mq.png" alt="名企" class="png" width="14">';
					}
					if(val.rating_logo){
						html += '<img src="' + val.rating_logo + '" style="vertical-align:middle" width="12" height="12" />';
					}
					if(val.yyzz_status == 1){
						html += '<i class="job_qy_rz_icon"></i>';
					}
					html += '</div></a></div>';
				});
				miniRefresh.endUpLoading(false);
			}else{
				miniRefresh.endUpLoading(true);
			}
		}
		document.getElementById('listdiv').insertAdjacentHTML('beforeend', html);
	});
}