(function ($) {
    'use strict';

    var PAGE_SIZE = 10;
    var currentPage = 0;
    var currentInfoId = null;
    var requestSeq = 0;
    var moveTimer = null;
    var map = new AMap.Map('map-container', {
        zoom: 13,
        center: [x, y]
    });
    window.map = map;

    function searchType() {
        return $('#searchtype').val() || 'job';
    }

    function mapBounds() {
        var bounds = map.getBounds();
        return {
            southWest: bounds.getSouthWest(),
            northEast: bounds.getNorthEast()
        };
    }

    function setLoading(loading) {
        $('#map .map-loader').toggle(loading);
        $('#map .map-loader-box').toggle(loading);
    }

    function emptyText() {
        return searchType() === 'company' ? '附近暂无企业' : '附近暂无职位';
    }

    function loadMapData(page) {
        var bounds = mapBounds();
        var requestId = ++requestSeq;
        currentPage = page || 0;
        setLoading(true);

        $.ajax({
            url: getdataurl,
            type: 'POST',
            dataType: 'json',
            data: {
                keyword: $.trim($('#keyword').val()),
                min_lng: bounds.southWest.lng,
                max_lng: bounds.northEast.lng,
                min_lat: bounds.southWest.lat,
                max_lat: bounds.northEast.lat,
                page: currentPage,
                type: searchType()
            }
        }).done(function (data) {
            if (requestId !== requestSeq) {
                return;
            }
            renderMapResult(data || {});
        }).fail(function () {
            if (requestId !== requestSeq) {
                return;
            }
            $('#newcontent').html('<div class="map_no_job">加载失败，请稍后重试</div>');
            $('#Pagination').hide();
        }).always(function () {
            if (requestId === requestSeq) {
                setLoading(false);
            }
        });
    }

    function renderMapResult(data) {
        var list = data.list || [];
        var type = searchType();
        var $content = $('#newcontent').empty();

        map.clearMap();
        currentInfoId = null;

        if (!list.length) {
            $content.html('<div class="map_no_job">' + emptyText() + '</div>');
            $('#Pagination').hide();
            return;
        }

        $.each(list, function (_, item) {
            if (!item.x || !item.y) {
                return;
            }
            var infoWindow = createInfoWindow(item);
            var marker = new AMap.Marker({
                position: new AMap.LngLat(item.x, item.y)
            });
            var card = type === 'company' ? createCompanyCard(item) : createJobCard(item);

            marker.on('click', function () {
                currentInfoId = item.id;
                infoWindow.open(map, [item.x, item.y]);
            });
            map.add(marker);

            card.on('mouseenter', function () {
                card.css('background-color', '#fff');
            }).on('mouseleave', function () {
                card.css('background-color', '#f8f8f8');
            }).on('click', function (event) {
                if ($(event.target).is('a')) {
                    return;
                }
                currentInfoId = item.id;
                infoWindow.open(map, [item.x, item.y]);
            });

            $content.append(card);
        });

        renderPagination(data.count || 0);
    }

    function createInfoWindow(item) {
        var jobs = item.joblist || [];
        var $content = $('<dl class="markerinfo"></dl>');
        var $title = $('<dt></dt>').append(
            $('<a target="_blank"></a>').attr('href', item.com_url).text(item.name)
        );
        if (item.is_vip) {
            $title.find('a').append('<span class="vip" title="VIP会员企业">V</span>');
        }
        $content.append($title);
        $.each(jobs, function (_, job) {
            $content.append(
                $('<dd style="height:25px;"></dd>').append(
                    $('<a target="_blank"></a>').attr('href', job.job_url).text(job.name)
                )
            );
        });
        $content.append(
            $('<dd class="more"></dd>').append(
                $('<a target="_blank">更多…</a>').attr('href', item.com_url)
            )
        );
        return new AMap.InfoWindow({
            content: $content[0],
            offset: [0, -30]
        });
    }

    function createCompanyCard(item) {
        var jobs = item.joblist || [];
        var $card = $('<div class="map_comlist"></div>');
        var $box = $('<div class="map_comlist_c"></div>');
        $box.append($('<a class="map_comname" target="_blank"></a>').attr('href', item.com_url).text(item.name));

        if (jobs.length) {
            $.each(jobs, function (_, job) {
                var $job = $('<div class="map_comlist_job"></div>');
                $job.append($('<a class="map_comlist_jobname" target="_blank"></a>').attr('href', job.job_url).text(job.name));
                var $meta = $('<div class="map_comlist_jobinfo"></div>');
                $meta.append($('<span class="map_comlist_jobxz"></span>').text(job.job_salary || ''));
                if (job.job_exp) {
                    $meta.append('<span class="map_comlist_jobline">|</span>', document.createTextNode(job.job_exp + '经验'));
                }
                if (job.job_edu) {
                    $meta.append('<span class="map_comlist_jobline">|</span>', document.createTextNode(job.job_edu + '学历'));
                }
                $job.append($meta);
                $job.append($('<a class="new_map_lookjob" target="_blank">查看</a>').attr('href', job.job_url));
                $box.append($job);
            });
        } else {
            $box.append('<div class="map_comlist_job">暂无职位</div>');
        }
        return $card.append($box);
    }

    function createJobCard(item) {
        var job = (item.joblist || [])[0] || {};
        var $card = $('<div class="new_map_job"></div>');
        $card.append(
            $('<div class="new_map_jobname"></div>').append(
                $('<a target="_blank"></a>').attr('href', job.job_url).text(job.name || '')
            )
        );

        var $meta = $('<div class="new_map_info"></div>');
        $meta.append($('<span class="new_map_info_xz"></span>').text(job.job_salary || ''));
        if (job.job_exp) {
            $meta.append('<span class="new_map_jobline">|</span>', document.createTextNode(job.job_exp + '经验'));
        }
        if (job.job_edu) {
            $meta.append('<span class="new_map_jobline">|</span>', document.createTextNode(job.job_edu + '学历'));
        }
        $card.append($meta);
        $card.append(
            $('<div class="new_map_comname"></div>').append(
                $('<a target="_blank"></a>').attr('href', item.com_url).text(item.name)
            )
        );
        $.each(job.welfare_n || [], function (_, welfare) {
            if ($.trim(welfare)) {
                $card.append($('<a class="new_map_comfl"></a>').text(welfare));
            }
        });
        $card.append($('<a class="new_map_look" target="_blank">查看</a>').attr('href', job.job_url));
        return $card;
    }

    function renderPagination(total) {
        var pages = Math.ceil(total / PAGE_SIZE);
        var $pagination = $('#Pagination');
        if (pages <= 1) {
            $pagination.hide().empty();
            return;
        }
        var html = [];
        if (currentPage > 0) {
            html.push('<a href="javascript:;" class="prev" data-page="' + (currentPage - 1) + '"></a>');
        }
        for (var page = 0; page < pages; page++) {
            if (page === 0 || page === pages - 1 || Math.abs(page - currentPage) <= 2) {
                html.push(page === currentPage
                    ? '<span class="current">' + (page + 1) + '</span>'
                    : '<a href="javascript:;" data-page="' + page + '">' + (page + 1) + '</a>');
            }
        }
        if (currentPage < pages - 1) {
            html.push('<a href="javascript:;" class="next" data-page="' + (currentPage + 1) + '"></a>');
        }
        $pagination.html(html.join('')).show().off('click').on('click', 'a[data-page]', function () {
            loadMapData(parseInt($(this).attr('data-page'), 10));
        });
    }

    function searchByKeyword() {
        loadMapData(0);
    }

    function searchWhenMoving() {
        clearTimeout(moveTimer);
        moveTimer = setTimeout(function () {
            loadMapData(0);
        }, 600);
    }

    function centerMap(position, zoom) {
        x = position.lng;
        y = position.lat;
        map.setZoomAndCenter(zoom || 13, new AMap.LngLat(x, y));
        loadMapData(0);
    }

    function locateByIp() {
        AMap.plugin('AMap.CitySearch', function () {
            new AMap.CitySearch().getLocalCity(function (status, result) {
                if (status === 'complete' && result && result.center) {
                    map.setZoomAndCenter(result.level || 12, result.center);
                    loadMapData(0);
                }
            });
        });
    }

    function getCurrentLocation() {
        if (!navigator.geolocation) {
            locateByIp();
            return;
        }
        AMap.plugin('AMap.Geolocation', function () {
            new AMap.Geolocation({
                enableHighAccuracy: true,
                timeout: 8000
            }).getCurrentPosition(function (status, result) {
                if (status === 'complete' && result.info === 'SUCCESS') {
                    centerMap(result.position, 13);
                } else {
                    locateByIp();
                }
            });
        });
    }

    map.on('dragend', searchWhenMoving);
    map.on('zoomend', searchWhenMoving);

    $(function () {
        $('.changetype').on('click', function () {
            var type = $(this).attr('data-type');
            $('#searchtype').val(type);
            $('#keyword').val('').attr('placeholder', type === 'company' ? '请输入企业名称' : '请输入职位名称');
            $('.new_mapbox_nav li').removeClass('new_mapbox_nav_cur');
            $('#t' + type).addClass('new_mapbox_nav_cur');
            loadMapData(0);
        });

        $('#submitbutton').on('click', searchByKeyword);
        $('#keyword').on('keydown', function (event) {
            if (event.keyCode === 13) {
                event.preventDefault();
                searchByKeyword();
            }
        });

        loadMapData(0);
        getCurrentLocation();
    });
})(jQuery);
