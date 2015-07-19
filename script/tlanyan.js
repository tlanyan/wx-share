(function (w, $, tlanyan) {
    "use strict";
    /**
     * 微信jsapi
     *
     * @param list 调用的api接口列表
     * @param callback 微信api准备后的回调接口
     * @param debug 是否开启debug模式，默认为否
     */
    tlanyan.weixinJsApi = function (list, callback, debug) {
        var db = debug===undefined ? false : debug;
        $.ajax({
            url: 'sign-package.php', // 获取签名的网址，请根据后台网址调整
            dataType: "json",
            data: {url: window.location.href.split("#")[0]}, // url为当前的url，所在域名需要在微信后台设置的js调用域名中！！
            success: function(data) {
                wx.config({
                    debug: db,
                    appId: data.appId,
                    timestamp: data.timestamp,
                    nonceStr: data.nonceStr,
                    signature: data.signature,
                    jsApiList: list
                });
                wx.ready(function() {
                    callback();
                });
            },
            error: function (a, b, c) {
                alert(a.responseText);
            }
        });
    }
})(window, window.jQuery, window.tlanyan || (window.tlanyan = {}));
