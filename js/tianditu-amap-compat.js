(function (window, document) {
    'use strict';

    if (window.AMap && window.AMap.__tiandituCompat) {
        return;
    }
    if (typeof window.T === 'undefined') {
        window.console && console.error('Tianditu API is not available.');
        return;
    }

    var DEFAULT_LNG = 116.398066;
    var DEFAULT_LAT = 39.917132;
    var PI = Math.PI;
    var A = 6378245.0;
    var EE = 0.00669342162296594323;

    function outOfChina(lng, lat) {
        return lng < 72.004 || lng > 137.8347 || lat < 0.8293 || lat > 55.8271;
    }

    function transformLat(lng, lat) {
        var ret = -100.0 + 2.0 * lng + 3.0 * lat + 0.2 * lat * lat + 0.1 * lng * lat + 0.2 * Math.sqrt(Math.abs(lng));
        ret += (20.0 * Math.sin(6.0 * lng * PI) + 20.0 * Math.sin(2.0 * lng * PI)) * 2.0 / 3.0;
        ret += (20.0 * Math.sin(lat * PI) + 40.0 * Math.sin(lat / 3.0 * PI)) * 2.0 / 3.0;
        ret += (160.0 * Math.sin(lat / 12.0 * PI) + 320 * Math.sin(lat * PI / 30.0)) * 2.0 / 3.0;
        return ret;
    }

    function transformLng(lng, lat) {
        var ret = 300.0 + lng + 2.0 * lat + 0.1 * lng * lng + 0.1 * lng * lat + 0.1 * Math.sqrt(Math.abs(lng));
        ret += (20.0 * Math.sin(6.0 * lng * PI) + 20.0 * Math.sin(2.0 * lng * PI)) * 2.0 / 3.0;
        ret += (20.0 * Math.sin(lng * PI) + 40.0 * Math.sin(lng / 3.0 * PI)) * 2.0 / 3.0;
        ret += (150.0 * Math.sin(lng / 12.0 * PI) + 300.0 * Math.sin(lng / 30.0 * PI)) * 2.0 / 3.0;
        return ret;
    }

    function wgs84ToGcj02(lng, lat) {
        lng = Number(lng);
        lat = Number(lat);
        if (!isFinite(lng) || !isFinite(lat) || outOfChina(lng, lat)) {
            return { lng: lng, lat: lat };
        }
        var dLat = transformLat(lng - 105.0, lat - 35.0);
        var dLng = transformLng(lng - 105.0, lat - 35.0);
        var radLat = lat / 180.0 * PI;
        var magic = Math.sin(radLat);
        magic = 1 - EE * magic * magic;
        var sqrtMagic = Math.sqrt(magic);
        dLat = (dLat * 180.0) / ((A * (1 - EE)) / (magic * sqrtMagic) * PI);
        dLng = (dLng * 180.0) / (A / sqrtMagic * Math.cos(radLat) * PI);
        return { lng: lng + dLng, lat: lat + dLat };
    }

    function gcj02ToWgs84(lng, lat) {
        lng = Number(lng);
        lat = Number(lat);
        if (!isFinite(lng) || !isFinite(lat) || outOfChina(lng, lat)) {
            return { lng: lng, lat: lat };
        }
        var wgsLng = lng;
        var wgsLat = lat;
        for (var i = 0; i < 3; i += 1) {
            var converted = wgs84ToGcj02(wgsLng, wgsLat);
            wgsLng -= converted.lng - lng;
            wgsLat -= converted.lat - lat;
        }
        return { lng: wgsLng, lat: wgsLat };
    }

    function isTdtLngLat(value) {
        return value && typeof value.getLng === 'function' && typeof value.getLat === 'function';
    }

    function LngLat(lng, lat) {
        if (Array.isArray(lng)) {
            lat = lng[1];
            lng = lng[0];
        } else if (lng && typeof lng === 'object') {
            lat = lng.lat !== undefined ? lng.lat : lng.getLat && lng.getLat();
            lng = lng.lng !== undefined ? lng.lng : lng.getLng && lng.getLng();
        }
        this.lng = Number(lng);
        this.lat = Number(lat);
    }

    LngLat.prototype.getLng = function () {
        return this.lng;
    };

    LngLat.prototype.getLat = function () {
        return this.lat;
    };

    LngLat.prototype.toString = function () {
        return this.lng + ',' + this.lat;
    };

    LngLat.prototype.offset = function (x, y) {
        return new LngLat(this.lng + Number(x || 0), this.lat + Number(y || 0));
    };

    function normalizeLngLat(value) {
        if (value instanceof LngLat) {
            return value;
        }
        if (isTdtLngLat(value)) {
            var gcjFromTdt = wgs84ToGcj02(value.getLng(), value.getLat());
            return new LngLat(gcjFromTdt.lng, gcjFromTdt.lat);
        }
        return new LngLat(value || [DEFAULT_LNG, DEFAULT_LAT]);
    }

    function toTdtLngLat(value) {
        var point = normalizeLngLat(value);
        var wgs = gcj02ToWgs84(point.lng, point.lat);
        return new window.T.LngLat(wgs.lng, wgs.lat);
    }

    function fromTdtLngLat(value) {
        if (!value) {
            return null;
        }
        var lng = typeof value.getLng === 'function' ? value.getLng() : value.lng;
        var lat = typeof value.getLat === 'function' ? value.getLat() : value.lat;
        var gcj = wgs84ToGcj02(lng, lat);
        return new LngLat(gcj.lng, gcj.lat);
    }

    function addEvent(target, name, handler) {
        if (target && typeof target.addEventListener === 'function') {
            target.addEventListener(name, handler);
        } else if (target && typeof target.on === 'function') {
            target.on(name, handler);
        }
    }

    function removeEvent(target, name, handler) {
        if (target && typeof target.removeEventListener === 'function') {
            target.removeEventListener(name, handler);
        } else if (target && typeof target.off === 'function') {
            target.off(name, handler);
        }
    }

    function TMap(container, options) {
        options = options || {};
        this._handlers = {};
        this._overlays = [];
        this._container = typeof container === 'string' ? document.getElementById(container) : container;
        this._map = new window.T.Map(this._container);
        var center = normalizeLngLat(options.center || [DEFAULT_LNG, DEFAULT_LAT]);
        var zoom = Number(options.zoom || 13);
        this._map.centerAndZoom(toTdtLngLat(center), zoom);

        var self = this;
        addEvent(this._map, 'click', function (event) {
            self._emit('click', {
                lnglat: fromTdtLngLat(event.lnglat || event.lngLat),
                originalEvent: event
            });
        });
        addEvent(self._map, 'moveend', function (event) {
            self._emit('moveend', event || {});
            self._emit('dragend', event || {});
        });
        addEvent(self._map, 'zoomend', function (event) {
            self._emit('zoomend', event || {});
        });
        ['touchmove', 'touchend'].forEach(function (name) {
            addEvent(self._container, name, function (event) {
                self._emit(name, { originalEvent: event });
            });
        });
        window.setTimeout(function () {
            self._emit('complete', {});
        }, 0);
    }

    TMap.prototype._emit = function (name, payload) {
        (this._handlers[name] || []).slice().forEach(function (handler) {
            handler.call(this, payload || {});
        }, this);
    };

    TMap.prototype.on = function (name, handler) {
        this._handlers[name] = this._handlers[name] || [];
        this._handlers[name].push(handler);
        return this;
    };

    TMap.prototype.off = function (name, handler) {
        if (!this._handlers[name]) {
            return this;
        }
        this._handlers[name] = handler
            ? this._handlers[name].filter(function (item) { return item !== handler; })
            : [];
        return this;
    };

    TMap.prototype.add = function (overlay) {
        var target = overlay && overlay._marker ? overlay._marker : overlay;
        var ti = overlay && overlay._tiandituObject ? overlay._tiandituObject : target;
        if (ti && typeof this._map.addOverLay === 'function') {
            this._map.addOverLay(ti);
            this._overlays.push(ti);
            if (overlay instanceof Marker) {
                overlay._map = this;
            }
        }
        return this;
    };

    TMap.prototype.remove = function (overlay) {
        var ti = overlay && overlay._tiandituObject ? overlay._tiandituObject : overlay;
        if (ti && typeof this._map.removeOverLay === 'function') {
            this._map.removeOverLay(ti);
        }
        return this;
    };

    TMap.prototype.clearMap = function () {
        if (typeof this._map.clearOverLays === 'function') {
            this._map.clearOverLays();
        }
        this._overlays = [];
        return this;
    };

    TMap.prototype.setZoomAndCenter = function (zoom, center) {
        this._map.centerAndZoom(toTdtLngLat(center), Number(zoom));
        return this;
    };

    TMap.prototype.setCenter = function (center, zoom) {
        zoom = zoom === undefined
            ? (typeof this._map.getZoom === 'function' ? this._map.getZoom() : 13)
            : Number(zoom);
        this._map.centerAndZoom(toTdtLngLat(center), zoom);
        return this;
    };

    TMap.prototype.getCenter = function () {
        return fromTdtLngLat(this._map.getCenter && this._map.getCenter());
    };

    TMap.prototype.setZoom = function (zoom) {
        if (typeof this._map.setZoom === 'function') {
            this._map.setZoom(Number(zoom));
        }
        return this;
    };

    TMap.prototype.getZoom = function () {
        return typeof this._map.getZoom === 'function' ? this._map.getZoom() : 13;
    };

    TMap.prototype.panTo = function (center) {
        if (typeof this._map.panTo === 'function') {
            this._map.panTo(toTdtLngLat(center));
        } else {
            this.setCenter(center);
        }
        return this;
    };

    TMap.prototype.panBy = function (x, y) {
        if (typeof this._map.panBy === 'function') {
            this._map.panBy(Number(x), Number(y));
        }
        return this;
    };

    TMap.prototype.setCity = function (city) {
        var self = this;
        var search = new CitySearch();
        search.getLocalCity(function (status, result) {
            if (status === 'complete' && result && result.center) {
                self.setZoomAndCenter(result.level || 12, result.center);
            }
        });
        return this;
    };

    TMap.prototype.setMapType = function () {
        return this;
    };

    TMap.prototype.setStatus = function (status) {
        status = status || {};
        var map = this._map;
        var methodPairs = [
            ['dragEnable', 'enableDrag', 'disableDrag'],
            ['keyboardEnable', 'enableKeyboard', 'disableKeyboard'],
            ['scrollWheel', 'enableScrollWheelZoom', 'disableScrollWheelZoom'],
            ['doubleClickZoom', 'enableDoubleClickZoom', 'disableDoubleClickZoom']
        ];
        methodPairs.forEach(function (pair) {
            if (status[pair[0]] === undefined) {
                return;
            }
            var method = status[pair[0]] ? pair[1] : pair[2];
            if (typeof map[method] === 'function') {
                map[method]();
            }
        });
        return this;
    };

    TMap.prototype.getSize = function () {
        return {
            width: this._container ? this._container.clientWidth : 0,
            height: this._container ? this._container.clientHeight : 0
        };
    };

    TMap.prototype.enableInertialDragging = function () {
        if (typeof this._map.enableInertia === 'function') {
            this._map.enableInertia();
        }
        return this;
    };

    TMap.prototype.enableContinuousZoom = function () {
        if (typeof this._map.enableContinuousZoom === 'function') {
            this._map.enableContinuousZoom();
        }
        return this;
    };

    TMap.prototype.setFitView = function () {
        return this;
    };

    TMap.prototype.resize = function () {
        if (typeof this._map.checkResize === 'function') {
            this._map.checkResize();
        }
        return this;
    };

    TMap.prototype.getBounds = function () {
        var bounds = this._map.getBounds && this._map.getBounds();
        if (!bounds) {
            return null;
        }
        function convert(point) {
            return point ? fromTdtLngLat(point) : null;
        }
        return {
            getSouthWest: function () {
                return convert(bounds.getSouthWest && bounds.getSouthWest());
            },
            getNorthEast: function () {
                return convert(bounds.getNorthEast && bounds.getNorthEast());
            },
            getCenter: function () {
                return convert(bounds.getCenter && bounds.getCenter());
            }
        };
    };

    TMap.prototype.addControl = function (control) {
        if (control && control._tiandituControl && typeof this._map.addControl === 'function') {
            this._map.addControl(control._tiandituControl);
        }
        return this;
    };

    TMap.prototype.removeControl = function (control) {
        if (control && control._tiandituControl && typeof this._map.removeControl === 'function') {
            this._map.removeControl(control._tiandituControl);
        }
        return this;
    };

    function Marker(options) {
        options = options || {};
        this._handlers = {};
        this._marker = new window.T.Marker(toTdtLngLat(options.position || [DEFAULT_LNG, DEFAULT_LAT]));
        this._tiandituObject = this._marker;
        var self = this;
        ['click', 'dblclick', 'mouseover', 'mouseout'].forEach(function (name) {
            addEvent(self._marker, name, function (event) {
                (self._handlers[name] || []).slice().forEach(function (handler) {
                    handler.call(self, event || {});
                }, self);
            });
        });
    }

    Marker.prototype.setPosition = function (position) {
        if (typeof this._marker.setLngLat === 'function') {
            this._marker.setLngLat(toTdtLngLat(position));
        }
        return this;
    };

    Marker.prototype.getPosition = function () {
        if (typeof this._marker.getLngLat === 'function') {
            return fromTdtLngLat(this._marker.getLngLat());
        }
        return null;
    };

    Marker.prototype.setMap = function (map) {
        if (map) {
            map.add(this);
        } else if (this._map) {
            this._map.remove(this);
        }
        this._map = map || null;
        return this;
    };

    Marker.prototype.setIcon = function () {
        return this;
    };

    Marker.prototype.setTitle = function () {
        return this;
    };

    Marker.prototype.show = function () {
        if (typeof this._marker.show === 'function') {
            this._marker.show();
        }
        return this;
    };

    Marker.prototype.hide = function () {
        if (typeof this._marker.hide === 'function') {
            this._marker.hide();
        }
        return this;
    };

    Marker.prototype.on = function (name, handler) {
        this._handlers[name] = this._handlers[name] || [];
        this._handlers[name].push(handler);
        return this;
    };

    Marker.prototype.off = function (name, handler) {
        if (!this._handlers[name]) {
            return this;
        }
        this._handlers[name] = handler
            ? this._handlers[name].filter(function (item) { return item !== handler; })
            : [];
        return this;
    };

    function InfoWindow(options) {
        options = options || {};
        this._content = options.content || '';
        this._handlers = {};
        this._tiandituObject = new window.T.InfoWindow(this._content, {
            offset: new window.T.Point(0, Number(options.offset && options.offset[1] || 0))
        });
        var self = this;
        addEvent(this._tiandituObject, 'close', function () {
            self._emit('close');
        });
    }

    InfoWindow.prototype._emit = function (name) {
        (this._handlers[name] || []).slice().forEach(function (handler) {
            handler({ type: name });
        });
    };

    InfoWindow.prototype.setContent = function (content) {
        this._content = content;
        if (typeof this._tiandituObject.setContent === 'function') {
            this._tiandituObject.setContent(content);
        }
        return this;
    };

    InfoWindow.prototype.open = function (map, position) {
        if (map && map._map && typeof map._map.openInfoWindow === 'function') {
            this._map = map;
            map._map.openInfoWindow(this._tiandituObject, toTdtLngLat(position));
        }
        return this;
    };

    InfoWindow.prototype.close = function () {
        if (this._map && this._map._map && typeof this._map._map.closeInfoWindow === 'function') {
            this._map._map.closeInfoWindow();
        }
        this._emit('close');
        return this;
    };

    InfoWindow.prototype.on = function (name, handler) {
        this._handlers[name] = this._handlers[name] || [];
        this._handlers[name].push(handler);
        return this;
    };

    function ToolBar() {
        this._tiandituControl = window.T.Control && window.T.Control.Zoom
            ? new window.T.Control.Zoom()
            : null;
    }

    function CitySearch() {}

    CitySearch.prototype.getLocalCity = function (callback) {
        callback = typeof callback === 'function' ? callback : function () {};
        if (window.T.LocalCity && window.T.LocalCity.prototype && typeof window.T.LocalCity.prototype.location === 'function') {
            var localCity = new window.T.LocalCity();
            var called = false;
            localCity.location(function (result) {
                if (called) {
                    return;
                }
                called = true;
                var center = result && result.lnglat ? fromTdtLngLat(result.lnglat) : null;
                callback(center ? 'complete' : 'error', {
                    city: result && (result.cityName || result.name || result.city) || '',
                    level: result && result.level || '',
                    center: center || new LngLat(DEFAULT_LNG, DEFAULT_LAT)
                });
            });
            return;
        }
        callback('error', {
            city: '',
            center: new LngLat(DEFAULT_LNG, DEFAULT_LAT)
        });
    };

    function Geolocation(options) {
        this._options = options || {};
    }

    Geolocation.prototype.isSupport = function () {
        return !!navigator.geolocation;
    };

    Geolocation.prototype.getCurrentPosition = function (callback) {
        callback = typeof callback === 'function' ? callback : function () {};
        if (!navigator.geolocation) {
            callback('error', { info: 'Geolocation is not supported.' });
            return;
        }
        var self = this;
        navigator.geolocation.getCurrentPosition(function (position) {
            var gcj = wgs84ToGcj02(position.coords.longitude, position.coords.latitude);
            var point = new LngLat(gcj.lng, gcj.lat);
            self._position = point;
            callback('complete', {
                position: point,
                accuracy: position.coords.accuracy,
                info: 'SUCCESS',
                location_type: 'html5'
            });
        }, function (error) {
            callback('error', { info: error.message, code: error.code });
        }, self._options);
    };

    Geolocation.prototype.getCityInfo = function (callback) {
        var search = new CitySearch();
        search.getLocalCity(callback);
    };

    Geolocation.prototype.getStatus = function () {
        return this._position ? 'complete' : 'idle';
    };

    var GeometryUtil = {
        distance: function (point1, point2) {
            var first = normalizeLngLat(point1);
            var second = normalizeLngLat(point2);
            var rad = Math.PI / 180;
            var dLat = (second.lat - first.lat) * rad;
            var dLng = (second.lng - first.lng) * rad;
            var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(first.lat * rad) * Math.cos(second.lat * rad) *
                Math.sin(dLng / 2) * Math.sin(dLng / 2);
            return 6371000 * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        }
    };

    function unsupportedSearch(options) {
        this._options = options || {};
    }

    unsupportedSearch.prototype.search = function (keyword, callback) {
        if (typeof callback === 'function') {
            callback('error', { info: 'This Tianditu compatibility feature is unavailable.' });
        }
    };

    unsupportedSearch.prototype.clear = function () {
        return this;
    };

    function Geocoder(options) {
        this._options = options || {};
    }

    Geocoder.prototype.getLocation = function (address, callback) {
        if (!window.T.Geocoder || typeof window.T.Geocoder !== 'function') {
            callback('error', { info: 'Tianditu geocoder is not loaded.' });
            return;
        }
        var geocoder = new window.T.Geocoder();
        geocoder.getPoint(address, function (result) {
            if (!result || !result.location) {
                callback('error', { info: 'Address not found.' });
                return;
            }
            callback('complete', {
                geocodes: [{
                    location: fromTdtLngLat(result.location),
                    formattedAddress: result.formatted_address || address
                }]
            });
        });
    };

    Geocoder.prototype.getAddress = function (position, callback) {
        if (!window.T.Geocoder || typeof window.T.Geocoder !== 'function') {
            callback('error', { info: 'Tianditu geocoder is not loaded.' });
            return;
        }
        var geocoder = new window.T.Geocoder();
        geocoder.getLocation(toTdtLngLat(position), function (result) {
            callback(result ? 'complete' : 'error', {
                regeocode: result && {
                    formattedAddress: result.formatted_address || '',
                    addressComponent: result.addressComponent || {}
                }
            });
        });
    };

    var AMap = window.AMap = window.AMap || {};
    AMap.__tiandituCompat = true;
    AMap.Map = TMap;
    AMap.Marker = Marker;
    AMap.LngLat = LngLat;
    AMap.InfoWindow = InfoWindow;
    AMap.ToolBar = ToolBar;
    AMap.CitySearch = CitySearch;
    AMap.Geolocation = Geolocation;
    AMap.Geocoder = Geocoder;
    AMap.PlaceSearch = unsupportedSearch;
    AMap.AutoComplete = unsupportedSearch;
    AMap.Transfer = unsupportedSearch;
    AMap.Driving = unsupportedSearch;
    AMap.GeometryUtil = GeometryUtil;
    AMap.TransferPolicy = { LEAST_TIME: 0 };
    AMap.DrivingPolicy = { LEAST_TIME: 0 };
    AMap.plugin = function (name, callback) {
        if (typeof callback === 'function') {
            callback();
        }
    };
})(window, document);
