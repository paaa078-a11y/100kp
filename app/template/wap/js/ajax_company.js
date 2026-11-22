var compage = 1;
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
	compage++;
	var searchurl = document.getElementById('searchurl').value;
	$.post(wapurl + 'index.php?c=company&a=ajaxLoad', {searchurl: searchurl,page: compage}, function(res){
		if(res){
			var html = '';
			var data = eval('(' + res + ')');
			if(data.length > 0){
				data.forEach(function(val){
					html += '<div class="com_list_box">';
					html += '<a href="' + val.wapurl + '" class="job_list" title="' + val.name + '">';
					html += '<div class="com_list_t_box">';
					html += '<div class="com_list_logo_box"><img src="' + val.logo + '"/></div>';
					html += '<div class="com_list_box_c"><h3>' + val.name;
					if(val.rating_logo){
						html += '<img src="' + val.rating_logo + '" width="12" height="12" />';
					}
					if(val.hotlogo){
						html += '<img src="' + weburl + '/app/template/default/images/firm_icon.png" />';
					}
					if(val.yyzz_status){
						html += '<i class="job_qy_rz_icon"></i>';
					}
					html += '</h3></div>';
					html += '<div class="com_list_box_js">';
					if(val.job_city_one){
						html += val.job_city_one;
					}
					if(val.job_city_two){
						html += '-' + val.job_city_two;
					}
					if(val.pr_n){
						html += '<i class="yun_newedition_jobline"></i>' + val.pr_n;
					}
					html += '</div></div>';
					if(val.jobname){
						html += '<div class="com_list_t_box_bq">';
						val.jobname.forEach(function(sval, index){
							if(index < 3){
								html += '<span class="com_list_box_joblist_a">' + sval + '</span>';
							}
						});
						html += '</div>';
					}
					if(val.jobnum > 0){
						html += '<a href="' + val.wapurl_showjob + '">';
						html += '<div class="com_list_box_joblist">';
						html += '<span class="com_list_box_jobzs">在招职位：<em class="com_list_box_jobncor">' + val.jobnum + '</em>个</span>';
						html += '<i class="com_list_box_jobzs_more"></i></div></a>';
					}
					html += '</div>';
				});
				miniRefresh.endUpLoading(false);
			}else{
				miniRefresh.endUpLoading(true);
			}
		}
		document.getElementById('listdiv').insertAdjacentHTML('beforeend', html);
	});
}