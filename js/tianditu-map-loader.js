(function (window, document) {
    'use strict';

    var currentScript = document.currentScript;
    if (!currentScript) {
        var scripts = document.getElementsByTagName('script');
        currentScript = scripts[scripts.length - 1];
    }

    function getQuery(name) {
        try {
            return new URL(currentScript.src, window.location.href).searchParams.get(name) || '';
        } catch (error) {
            var match = String(currentScript.src || '').match(new RegExp('[?&]' + name + '=([^&]+)'));
            return match ? decodeURIComponent(match[1]) : '';
        }
    }

    var key = getQuery('tk');
    var callbackName = getQuery('callback');
    var compatUrl = new URL('tianditu-amap-compat.js?v=1.0.3', currentScript.src).href;
    var apiUrl = 'https://api.tianditu.gov.cn/api?v=4.0&tk=' + encodeURIComponent(key);
    var originalAppendChild = null;

    // Tianditu inserts its modules as async scripts. Force its three module
    // scripts to execute in insertion order so military.js cannot run before
    // components.js has defined T.Tool.
    if (document.head && !document.head.__tiandituOrderPatch) {
        originalAppendChild = document.head.appendChild;
        document.head.appendChild = function (node) {
            if (node && node.tagName === 'SCRIPT' && /^https:\/\/api\.tianditu\.gov\.cn\/v4\.0\/(components|service|military)\.js(?:[?#]|$)/.test(node.src || '')) {
                node.async = false;
            }
            return originalAppendChild.call(this, node);
        };
        document.head.__tiandituOrderPatch = true;
    }

    function notifyReady() {
        if (callbackName && /^[A-Za-z_$][\w$]*$/.test(callbackName)) {
            var callback = window[callbackName];
            if (typeof callback === 'function') {
                callback();
            }
        }
        window.dispatchEvent(new CustomEvent('tianditu-map-ready'));
    }

    if (!key) {
        window.console && console.error('Tianditu browser key is missing.');
        notifyReady();
        return;
    }

    function loadScript(src) {
        return new Promise(function (resolve, reject) {
            var script = document.createElement('script');
            script.src = src;
            script.async = false;
            script.onload = resolve;
            script.onerror = reject;
            (document.head || document.body).appendChild(script);
        });
    }

    if (document.readyState === 'loading') {
        // Keep existing synchronous map initialisation working during page parsing.
        document.write('<script src="' + apiUrl + '"><\/script>');
        document.write('<script src="' + compatUrl + '"><\/script>');
        if (callbackName && /^[A-Za-z_$][\w$]*$/.test(callbackName)) {
            document.write('<script>(function(){var run=function(){if(typeof window["' + callbackName + '"]==="function"){window["' + callbackName + '"]();}};if(document.readyState==="loading"){document.addEventListener("DOMContentLoaded",run,{once:true});}else{run();}})();<\/script>');
        }
        return;
    }

    loadScript(apiUrl)
        .then(function () {
            return loadScript(compatUrl);
        })
        .then(notifyReady)
        .catch(function (error) {
            window.console && console.error('Tianditu map failed to load.', error);
            notifyReady();
        });
})(window, document);
