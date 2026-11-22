$(function () {
    //input输入实时事件
    //搜索框
    // $("#search").on('input propertychange',function(){
    //     var result = $(this).val();
    //     $("#pkeyword").attr('placeholder',result);
    // });
    //上下移动阻止冒泡
    // $(".move").click(function(e){
    // 	e.stopPropagation();
    // })
})

//折叠、radio点击事件
layui.use(['element', 'layer', 'form', 'carousel'], function () {
    var layer = layui.layer
        , form = layui.form
        , $ = layui.$;
    var carousel = layui.carousel;

    // 点击菜单同步变更配置开始
    $('#top').on('click', 'li', function () {
        let type = $(this).data('type');
        // $('#top').find('li').index(this); // 当前先中的是第几个
        // 菜单切换
        $('#top').find('li').removeClass('current');
        $(this).addClass('current');
        // 配置切换
        $('#bottom').find('li').removeClass('current');
        $('#' + type + 'set').addClass('current');
    })
    // 点击菜单同步变更配置结束

    // 拖动图标提示排序
    layer.tips('长按图标可拖动排序', $(".diysort").find('img').eq(0), {
        tips: [2, '#3595CC'],
        time: 5000
    });

    // 拖动排序开始
    $(".diysort").dragsort({
        dragSelector: "li>div",
        // dragSelectorExclude: "li[class='diynosort']",
        dragBetween: true,
        dragEnd: saveDiySort
    });

    function saveDiySort() {
        var pytoken = $("#pytoken").val();
        var sort = [];
        $("#top").find('li').each(function (index, obj) {
            sort.push($(obj).data('type'));
        })

        let type = '',
            pre = 'noticeshow'; // 上一个
        $('.diysort').find('li').each(function (index, obj) { // 拖动后手机演示对应变更
            type = $(obj).data('type');
            $('#' + pre).after($('#' + type + 'show'))
            pre = type + 'show';
        })

        $.post('index.php?m=admin_app_set&c=sort', {sort: sort, pytoken: pytoken}, function (data) {
        })
    }
    // 拖动排序结束

    // 自定义主题开始
    // 主颜色选择
    $(".maincolor span").bind('click', function () {
        let color = $(this).data('color');
        $("#maincolor").val(color);
        // $(".maincolor_cur").attr('style', 'background: ' + color + ';');
        $(".maincolor_cur").css({"background": color}); // 通过这个控制不会影响已有style
        // $(".style_top_item_curr::after").css('background', color); // 修改职位tab切换色
    })
    // 辅颜色选择
    $(".secondcolor span").bind('click', function () {
        let color = $(this).data('color');
        $("#secondcolor").val(color);
        $(".secondcolor_cur").attr('style', 'background: ' + color + ';');
        $(".secondcolor_font").css({"color": color}); // 修改职位字体色 通过这个控制不会影响已有style
    })
    // 自定义主题结束

    // 搜索开始
    // 搜索显示/隐藏
    form.on('switch(searchshow)', function (data) {
        if (data.elem.checked) {
            $("#searchshow").show();
        } else {
            $("#searchshow").hide();
        }
    });
    // 搜索组件样式切换
    form.on('radio(searchstyle)', function (data) {
        $('.searchstyle').hide();
        $('.searchstyle' + data.value).show();
    });
    // 文字变更
    // $("#search").on('input', function () {
    //     console.log($(this).val())
    // })
    // 搜索结束

    // 幻灯广告区开始
    // 广告显示/隐藏
    form.on('switch(membershow)', function (data) {
        if (data.elem.checked) {
            $("#membershow").show();
        } else {
            $("#membershow").hide();
        }
    });
    //图片轮播
    var carouselAdOptions = {
        elem: '#hdpicshow'
        , width: '100%'
        // , height: '85px'
        , height: $("#hdshow").find('img').eq(0).attr('height')
        , interval: 5000
    };
    var carouselAd = carousel.render(carouselAdOptions);
    // 广告样式切换
    form.on('radio(slideadstyle)', function (data) {
        console.log(data.value)
        if (data.value == 2) {
            var carouselHeight = '69px';
        } else {
            var carouselHeight = '85px';
        }
        $("#hdshow").find('img').attr('height', carouselHeight); // 修改图片高度
        //重置轮播
        carouselAd.reload(carouselAdOptions);
        $("#hdpicshow").css('height', carouselHeight); // 修改轮播外层DIV高度
    });
    // 幻灯广告区结束

    // 导航设置开始
    // 导航组件显示/隐藏
    form.on('switch(navshow)', function (data) {
        if (data.elem.checked) {
            $("#navshow").show();
        } else {
            $("#navshow").hide();
        }
    });
    // 样式切换
    form.on('radio(navstyle)', function (data) {
        $('.wap_mb_show_nav').hide();
        $('.navstyle' + data.value).show();
    });
    // 单个导航显示/隐藏
    form.on('switch(nav)', function (data) {
        let id = $(data.elem).data('id');
        if (data.elem.checked) {
            $("#navsortbox" + id).show();
            $(".linavshow" + id).show();
            $("#navdisplay" + id + "_hide").val(1);
        } else {
            $("#navsortbox" + id).hide();
            $(".linavshow" + id).hide();
            $("#navdisplay" + id + "_hide").val(2);
        }
    });
    // 增加导航
    var navidnum = 0;
    $("#navadd").click(function () {
        navidnum++;
        let navid = 'c' + navidnum;
        let html = '<div class="layui-form-item" id="n'+ navid +'">\n' +
            '                                    <div class="wap_mb_list" style="padding: 0;margin: 0;">\n' +
            '                                        <div class="wap_mb_list_hd_list">\n' +
            '                                            <div style="display: flex;">\n' +
            '                                                <div>\n' +
            '                                                    <input type="hidden" name="navid[]" value="">\n' +
            '                                                    <input type="hidden" name="navcid[]" value="' + navid + '">\n' + // 用来取新增导航的数据标识
            '                                                    <div class="app_set_img_item">\n' +
            '                                                        <span class="app_set_img_item_tit">图标：</span>\n' +
            '                                                        <div class="layui-upload">\n' +
            '                                                            <div class="wap_mb_list_hd_file_box">\n' +
            '                                                                <input type="file" name="navpic[]" multiple id="nav'+ navid +'"\n' +
            '                                                                       class="wap_mb_list_hd_file_text"\n' +
            '                                                                       onchange="showpic(this,\'imgn'+ navid +'\',\'navpicshow'+ navid +'\',\'class\')"/>+ 添加图标\n' +
            '                                                            </div>\n' +
            '                                                        </div>\n' +
            '                                                    </div>\n' +
            '                                                    <div class="app_set_img_item mt5">\n' +
            '                                                        <span class="app_set_img_item_tit">标题：</span>\n' +
            '                                                        <input name="navname[]" id="navname'+ navid +'"\n' +
            '                                                               value="" type="text" class="wap_mb_list_text" autocomplete="off">\n' +
            '                                                    </div>\n' +
            '                                                    <div class="app_set_img_item mt5">\n' +
            '                                                        <span class="app_set_img_item_tit">显示：</span>\n' +
            '                                                        <input type="checkbox" id="navdisplay'+ navid +'" \n' +
            '                                                               lay-skin="switch" lay-text="显示|隐藏" lay-filter="nav"\n' +
            '                                                               data-id="'+ navid +'"\n' +
            '                                                               checked/>\n' +
            '                                                        <input type="hidden" name="navdisplay[]" id="navdisplay'+ navid +'_hide"' +
            '                                                             value="1">\n' +
            '                                                    </div>\n' +
            '                                                    <div id="navsortbox'+ navid +'" class="app_set_img_item mt5">\n' +
            '                                                        <span class="app_set_img_item_tit">排序：</span>\n' +
            '                                                        <input id="navsort'+ navid +'" type="text" name="navsort[]" value="" class="wap_mb_list_text" autocomplete="off">\n' +
            '                                                    </div>\n' +
            '                                                    <div class="app_set_img_item mt5">\n' +
            '                                                        <span class="app_set_img_item_tit">类型：</span>\n' +
            '                                                        <div class="layui-input-block">\n' +
            '                                                            <input type="radio" name="navtype'+ navid +'" value="0" data-id="'+ navid +'" title="内部页面" checked="checked" lay-filter="navtype">\n' +
            '                                                            <input type="radio" name="navtype'+ navid +'" value="1" data-id="'+ navid +'" title="打开小程序(仅支持微信小程序)" lay-filter="navtype">\n' +
            '                                                        </div>\n' +
            '                                                    </div>\n' +
            '                                                    <div id="navtype1Box'+ navid +'" style="display: none;">\n' +
            '                                                        <div class="app_set_img_item mt5">\n' +
            '                                                            <span class="app_set_img_item_tit">Appid：</span>\n' +
            '                                                            <input name="navappid[]" id="navappid'+ navid +'" value="" type="text" class="wap_mb_list_text" autocomplete="off">\n' +
            '                                                        </div>\n' +
            '                                                        <div class="app_set_img_item mt5">\n' +
            '                                                            <span class="app_set_img_item_tit">路径：</span>\n' +
            '                                                            <input name="navurl[]" id="navurli'+ navid +'" disabled value="" type="text" class="wap_mb_list_text" autocomplete="off">\n' +
            '                                                        </div>\n' +
            '                                                    </div>\n' +
            '                                                    <div id="navtype0Box'+ navid +'" class="app_set_img_item mt5">\n' +
            '                                                        <span class="app_set_img_item_tit">链接：</span>\n' +
            '                                                        <div class="layui-input-block">\n' +
            '                                                            <select id="navurls'+ navid +'" name="navurl[]">\n' +
                                                                            $("#navurls70").html().replace('selected=""','') +
            '                                                            </select>\n' +
            '                                                          </div>\n' +
            '                                                    </div>\n' +
            '                                                </div>\n' +
            '                                                <div style="margin-left: 10px;">\n' +
            '                                                    <img src="" width="64" height="64" id="imgn'+ navid +'">\n' +
            '                                                </div>\n' +
            '                                            </div>\n' +
            '                                            <div class="app_set_img_item_tip">建议图标尺寸：不小于64*64像素</div>\n' +
            '                                            <div class="navdel" data-id="' + navid + '">删除</div>\n' +
            '                                        </div>\n' +
            '                                    </div>\n' +
            '                                </div>'
        $(".add_box").before(html);
        form.render('select'); //刷新select选择框渲染
        form.render('radio'); //刷新radio选择框渲染
        form.render('checkbox'); //刷新checkbox选择框渲染
    })
    // 删除导航
    $("body").on('click', '.navdel', function () {
        let linavshowobj = $(".linavshow" + $(this).data('id'));
        if (linavshowobj.length > 0) {
            linavshowobj.remove();
        }
        $(this).parent().parent().parent().remove();
    })
    // 导航设置结束

    // 职位专题开始
    form.on('switch(jobspecshow)', function (data) {
        if (data.elem.checked) {
            $("#jobspecshow").show();
        } else {
            $("#jobspecshow").hide();
        }
    });
    // 职位专题结束

    // 公告开始
    // 公告组件显示/隐藏
    form.on('switch(notice)', function (data) {
        if (data.elem.checked) {
            $("#noticeshow").show();
        } else {
            $("#noticeshow").hide();
        }
    });
    // 公告样式切换
    form.on('radio(noticestyle)', function (data) {
        $('.noticestyle').hide();
        $('.noticestyle' + data.value).show();
        $('.noticestyle-img').hide();
        $('.noticestyle' + data.value + '-img').show();
    });
    // 公告结束

    // 登录开始
    form.on('switch(login)', function (data) {
        if (data.elem.checked) {
            $("#loginshow").show();
        } else {
            $("#loginshow").hide();
        }
    });
    // 登录结束

    // 名企展示开始
    // 名企组件显示/隐藏
    form.on('switch(hotcom)', function (data) {
        if (data.elem.checked) {
            $("#hotcomshow").show();
        } else {
            $("#hotcomshow").hide();
        }
    });
    // 名企样式切换
    form.on('radio(hotcomstyle)', function (data) {
        console.log(data.value)
        $('.hotcomstyle').hide();
        $('.hotcomstyle' + data.value).show();
    });
    // 名企更多显示/隐藏
    form.on('switch(hotcommore)', function (data) {
        if (data.elem.checked) {
            $(".hotcommore").show();
        } else {
            $(".hotcommore").hide();
        }
    });
    // 名企展示结束

    // 通栏广告开始
    form.on('switch(adshow)', function (data) {
        if (data.elem.checked) {
            $("#adshow").show();
        } else {
            $("#adshow").hide();
        }
    });
    // 通栏广告结束

    // 热门职位（系统-类别-职位类别-首页推荐）开始
    // 组件显示/隐藏
    form.on('switch(hotjob)', function (data) {
        if (data.elem.checked) {
            $("#hotjobshow").show();
        } else {
            $("#hotjobshow").hide();
        }
    });
    // 更多显示/隐藏
    form.on('switch(hotjobmore)', function (data) {
        if (data.elem.checked) {
            $(".hotjobmore").show();
        } else {
            $(".hotjobmore").hide();
        }
    });
    // 热门职位（系统-类别-职位类别-首页推荐）结束

    // 资讯开始
    // 组件显示/隐藏
    form.on('switch(articleshow)', function (data) {
        if (data.elem.checked) {
            $('#articleshow').show();
        } else {
            $('#articleshow').hide();
        }
    });
    // 组件样式
    form.on('radio(articletype)', function (data) {
        $('.articletype').hide();
        $('.articletype' + data.value).show();
    });
    // 更多显示/隐藏
    form.on('switch(articlemore)', function (data) {
        if (data.elem.checked) {
            $(".articlemore").show();
        } else {
            $(".articlemore").hide();
        }
    });
    // 资讯结束

    // 招聘会开始
    // 组件显示/隐藏
    form.on('switch(zphshow)', function (data) {
        if (data.elem.checked) {
            $('#zphshow').show();
        } else {
            $('#zphshow').hide();
        }
    });
    // 更多显示/隐藏
    form.on('switch(zphmore)', function (data) {
        if (data.elem.checked) {
            $(".zphmore").show();
        } else {
            $(".zphmore").hide();
        }
    });
    // 招聘会结束

    // 职位列表开始
    // 组件显示/隐藏
    form.on('switch(job)', function (data) {
        if (data.elem.checked) {
            let nocheck = true;

            $('.jobsort').find('[type="checkbox"]').each(function(cindex, cobj) { // 系统配置tab
                if (cobj.checked) {
                    nocheck = false
                }
            })

            if (nocheck) { // 全部没选继续隐藏
                $('#jobshow').hide();
            } else {
                $('#jobshow').show();
            }
        } else {
            $('#jobshow').hide();
        }
    });
    // 组件样式
    form.on('radio(jobstyle)', function (data) {
        $('.jobstyle').hide();
        $('.jobstyle' + data.value).show();
    });
    // 首页tab显示隐藏
    form.on('checkbox(jobtabshow)', function (data) {
        let type = $(data.elem).attr('name'),
            check = data.elem.checked,
            nocheck = true;

        $('.jobsort').find('[type="checkbox"]').each(function(cindex, cobj) { // 系统配置tab
            if (cobj.checked) {
                nocheck = false
            }
        })

        if (nocheck) { // 全部没选隐藏
            $('#jobshow').hide();
        } else {
            if ($("input[name=job]").prop("checked")) {
                $('#jobshow').show();
            }
        }

        $('.jobstyle').each(function(index, obj) { // 4个样式
            let tabobj = $(obj).find('.style_top_item'),
                tabcur = null;
            tabobj.each(function (tindex, tobj) {
                if (type == $(tobj).data('type')) {
                    if (check) {
                        $(tobj).show();
                    } else {
                        $(tobj).hide();
                    }
                }
                if (!tabcur && $(tobj).find('i') && $(tobj).find('i').length > 0) {
                    tabcur = $(tobj).find('i'); // 保存选中样式
                }
            })
            tabobj.each(function (tindex, tobj) {
                if ($(tobj).css('display') != 'none') {
                    tabcur.remove(); // 移除不显示的选中样式
                    $(tobj).append(tabcur); // 选中样式变更
                    return false;
                }
            })
        })
    });
    // 推荐栏目调整
    $(".jobsort").dragsort({
        dragSelector: "li",
        dragSelectorExclude: 'input',
        dragBetween: true,
        dragEnd: saveJobSort
    });
    function saveJobSort() {
        $('.jobstyle').each(function(index, obj) { // 4个样式
            $('.jobsort').find('[name="jobsort[]"]').each(function(sindex, sobj) { // 系统配置的排序
                let tabobj = $(obj).find('.style_top_item'),
                    tabcur = null;
                tabobj.each(function(tindex, tobj) { // 4个tab
                    if (!tabcur && $(tobj).find('i') && $(tobj).find('i').length > 0) {
                        tabcur = $(tobj).find('i'); // 保存选中样式
                    }
                });
                tabobj.each(function(tindex, tobj) { // 4个tab
                    if ($(sobj).val() == $(tobj).data('type')) { // 排序移动
                        if (sindex != tindex) {
                            if (sindex == 0) {
                                // tabobj.eq(0).removeClass('style_top_item_curr'); // 移除第一个选中样式
                                // $(tobj).addClass('style_top_item_curr'); // 第一位增加选中样式
                                // tabcur = $(tobj).find('i'); // 保存选中样式
                                // tabcur.remove(); // 移除第一个选中样式
                                $(tobj).append(tabcur); // 将选种样式对像，移动至第一位增加选中样式
                            }
                            tabobj.eq(sindex).before(tabobj.eq(tindex));
                        }
                    }
                })
                $(obj).find('.style_top_item').each(function(tindex, tobj) { // 4个tab
                    if ($(tobj).css('display') != 'none') {
                        tabcur.remove(); // 移除不显示的选中样式
                        $(tobj).append(tabcur); // 选中样式变更
                        return false;
                    }
                })
            })
        })
    }
    // 首次加载tab选中样式动态变幻
    $('.jobstyle').each(function(index, obj) { // 4个样式
        let tabobj = $(obj).find('.style_top_item'),
            tabcur = null;
        tabobj.each(function (tindex, tobj) {
            if (!tabcur && $(tobj).find('i') && $(tobj).find('i').length > 0) {
                tabcur = $(tobj).find('i'); // 保存选中样式
            }
        })
        tabobj.each(function (tindex, tobj) {
            if ($(tobj).css('display') != 'none') {
                tabcur.remove(); // 移除不显示的选中样式
                $(tobj).append(tabcur); // 选中样式变更
                return false;
            }
        })
    })
    // 更多显示/隐藏
    // form.on('switch(jobmore)', function (data) {
    //     if (data.elem.checked) {
    //         $(".jobmore").show();
    //     } else {
    //         $(".jobmore").hide();
    //     }
    // });
    // 职位列表结束

    // 站长资质开始
    form.on('switch(syszzmore)', function (data) {
        if (data.elem.checked) {
            $("#syszzshow").show();
        } else {
            $("#syszzshow").hide();
        }
    });
    // 站长资质结束

    // 浮动客服开始
    form.on('switch(cshow)', function (data) {
        if (data.elem.checked) {
            $(".connectshow").show();
        } else {
            $(".connectshow").hide();
        }
    });
    // 浮动客服结束

    // 底部导航开始
    // 导航显示/隐藏
    form.on('switch(footshow)', function (data) {
        if (data.elem.checked) {
            $('.footstyle').show();
        } else {
            $('.footstyle').hide();
        }
    });
    // 样式切换
    form.on('radio(footstyle)', function (data) {
        $('.appfooter').hide();
        $('.footstyle' + data.value).show();
    });
    // 底部导航结束

    form.on('radio(timeheader)', function (data) {
        if (data.value == 1) {
            $("#timeheader").show();
        } else {
            $("#timeheader").hide();
        }
    });
    form.on('radio(usertype)', function (data) {
        if (data.value == 1) {
            $("#usermember").show();
            $("#commember").hide();
        } else {
            $("#usermember").hide();
            $("#commember").show();
        }
    });
    form.on('radio(login)', function (data) {
        if (data.value == 1) {
            $("#login").show();
        } else {
            $("#login").hide();
        }
    });
    form.on('radio(hdshow)', function (data) {
        if (data.value == 1) {
            $(".wap_mb_show_hdp").show();
        } else {
            $(".wap_mb_show_hdp").hide();
        }
    });
    form.on('radio(rewardjob)', function (data) {
        if (data.value == 1) {
            $("#rewardjob").show();
        } else {
            $("#rewardjob").hide();
        }
    });
    form.on('checkbox(rewardjobsalary)', function (data) {
        if (data.elem.checked == true) {
            $(".rewardjobsalary").show();
        } else {
            $(".rewardjobsalary").hide();
        }
    });
    form.on('checkbox(rewardjobreward)', function (data) {
        if (data.elem.checked == true) {
            $(".rewardjobreward").show();
        } else {
            $(".rewardjobreward").hide();
        }
    });
    form.on('radio(rewardjobmore)', function (data) {
        if (data.value == 1) {
            $(".rewardjobmore").show();
        } else {
            $(".rewardjobmore").hide();
        }
    });
    form.on('checkbox(jobsalary)', function (data) {
        if (data.elem.checked == true) {
            $("#jobsalary").show();
        } else {
            $("#jobsalary").hide();
        }
    });
    form.on('checkbox(jobcity)', function (data) {
        if (data.elem.checked == true) {
            $("#jobcity").show();
        } else {
            $("#jobcity").hide();
        }
    });
    form.on('checkbox(jobdate)', function (data) {
        if (data.elem.checked == true) {
            $("#jobdate").show();
        } else {
            $("#jobdate").hide();
        }
    });
    form.on('checkbox(jobwelfare)', function (data) {
        if (data.elem.checked == true) {
            $("#jobwelfare").show();
        } else {
            $("#jobwelfare").hide();
        }
    });
    form.on('radio(jobmore)', function (data) {
        if (data.value == 1) {
            $(".jobmore").show();
        } else {
            $(".jobmore").hide();
        }
    });
    form.on('checkbox(connect)', function (data) {
        var name = data.elem.dataset.name;

        if (data.elem.checked == true) {
            $("#" + name).show();
        } else {
            $("#" + name).hide();
        }
    });
    // form.on('checkbox(zphtime)', function (data) {
    //     if (data.elem.checked == true) {
    //         $("#zphtime").show();
    //     } else {
    //         $("#zphtime").hide();
    //     }
    // });
    // form.on('checkbox(zphplace)', function (data) {
    //     if (data.elem.checked == true) {
    //         $("#zphplace").show();
    //     } else {
    //         $("#zphplace").hide();
    //     }
    // });
    //咨询

    form.on('radio(navtype)', function (data) {

        var navid = data.elem.dataset.id;

        $(`#navappid${navid}`).attr("disabled", "disabled");

        $(`#navurli${navid}`).attr("disabled", "disabled");

        $(`#navurls${navid}`).attr("disabled", "disabled");

        $(`#navtype0Box${navid}`).hide();
        $(`#navtype1Box${navid}`).hide();

        $(`#navtype${data.value}Box${navid}`).show();

        if (data.value == 1) {

            $(`#navappid${navid}`).attr("disabled", false);

            $(`#navurli${navid}`).attr("disabled", false);
        } else {
            $(`#navurls${navid}`).attr("disabled", false);
            form.render('select'); // 重新渲染select组件
        }

    });

    //职位类别
    form.on('radio(jobclassone)', function (data) {
        if (data.value == 1) {
            $("#twojobshow").show();
            $("#twojobnum").show();
            $("#morejob").show();
            $(".jobclassone").show();
        } else {
            $("#twojobshow").hide();
            $("#twojobnum").hide();
            $("#morejob").hide();
            $(".jobclassone").hide();
        }
    });
    form.on('checkbox(jobclassonenumall)', function (data) {
        if (data.elem.checked == true) {
            $("#jobclassonenum").val('');
            $("#jobclassonenum").attr('disabled', 'disabled');
        } else {
            $("#jobclassonenum").removeAttr('disabled');
        }
    });
    form.on('radio(jobclasstwo)', function (data) {
        if (data.value == 1) {
            $(".jobclasstwo").show();
        } else {
            $(".jobclasstwo").hide();
        }
    });
    form.on('checkbox(jobclasstwonumall)', function (data) {
        if (data.elem.checked == true) {
            $("#jobclasstwonum").val('');
            $("#jobclasstwonum").attr('disabled', 'disabled');
        } else {
            $("#jobclasstwonum").removeAttr('disabled');
        }
    });
    form.on('radio(jobclassmore)', function (data) {
        if (data.value == 1) {
            $(".jobclassmore").show();
        } else {
            $(".jobclassmore").hide();
        }
    });
});
//图片轮播
layui.use(['carousel'], function () {
    var carousel = layui.carousel;

    carousel.render({
        elem: '#hdpicshow'
        , width: '100%'
        , height: '130px'
        , interval: 5000
    });
});

//选择主题颜色
function getcolor(id) {
    $(".js_change_color").removeClass("selected");
    $(".bg" + id).addClass("selected");
    $(".wap_header").attr('class', "wap_header bg" + id + " selected");
    $("#color").val(id);
    $(".yun_wapheader").attr('class', "yun_wapheader bg" + id);
    $(".yun_new_indexnav").attr('class', "yun_new_indexnav bg" + id);
}

//预览图片
function showpic(fileDom, imgid, pimgid='', ptype='id') {
    //判断是否支持FileReader
    if (window.FileReader) {
        var reader = new FileReader();
    } else {
        layer.msg("您的设备不支持图片预览功能，如需该功能请升级您的设备！", 2, 8);
    }
    //获取文件
    var file = fileDom.files[0];
    var imageType = /^image\//;
    //是否是图片
    if (!imageType.test(file.type)) {
        layer.msg("请选择图片！", 2, 8);
        return;
    }
    //读取完成
    reader.onload = function (e) {

        //获取图片dom
        var img = document.getElementById(imgid);
        if (pimgid) {
            console.log(pimgid, ptype, document.getElementsByClassName(pimgid))
            if (ptype == 'class') {
                var pimg = document.getElementsByClassName(pimgid);
                if (pimg) {
                    for (var i=0; i<pimg.length; i++) { // 根据class对象批量操作，需要循环
                        pimg[i].src = e.target.result;
                    }
                }
            } else {
                var pimg = document.getElementById(pimgid);
                if (pimg) {
                    pimg.src = e.target.result;
                }
            }
        }
        //图片路径设置为读取的图片
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
}

// 文字修改
function changeInput() {

}
