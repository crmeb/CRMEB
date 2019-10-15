!function(a,b){"function"==typeof define&&(define.amd||define.cmd)?define(function(){return b(a)}):b(a,!0)}(this,function(a,b){function c(b,c,d){a.WeixinJSBridge?WeixinJSBridge.invoke(b,e(c),function(a){h(b,a,d)}):k(b,d)}function d(b,c,d){a.WeixinJSBridge?WeixinJSBridge.on(b,function(a){d&&d.trigger&&d.trigger(a),h(b,a,c)}):d?k(b,d):k(b,c)}function e(a){return a=a||{},a.appId=D.appId,a.verifyAppId=D.appId,a.verifySignType="sha1",a.verifyTimestamp=D.timestamp+"",a.verifyNonceStr=D.nonceStr,a.verifySignature=D.signature,a}function f(a){return{timeStamp:a.timestamp+"",nonceStr:a.nonceStr,"package":a["package"],paySign:a.paySign,signType:a.signType||"SHA1"}}function g(a){return a.postalCode=a.addressPostalCode,delete a.addressPostalCode,a.provinceName=a.proviceFirstStageName,delete a.proviceFirstStageName,a.cityName=a.addressCitySecondStageName,delete a.addressCitySecondStageName,a.countryName=a.addressCountiesThirdStageName,delete a.addressCountiesThirdStageName,a.detailInfo=a.addressDetailInfo,delete a.addressDetailInfo,a}function h(a,b,c){"openEnterpriseChat"==a&&(b.errCode=b.err_code),delete b.err_code,delete b.err_desc,delete b.err_detail;var d=b.errMsg;d||(d=b.err_msg,delete b.err_msg,d=i(a,d),b.errMsg=d),c=c||{},c._complete&&(c._complete(b),delete c._complete),d=b.errMsg||"",D.debug&&!c.isInnerInvoke&&alert(JSON.stringify(b));var e=d.indexOf(":"),f=d.substring(e+1);switch(f){case"ok":c.success&&c.success(b);break;case"cancel":c.cancel&&c.cancel(b);break;default:c.fail&&c.fail(b)}c.complete&&c.complete(b)}function i(a,b){var c=a,d=q[c];d&&(c=d);var e="ok";if(b){var f=b.indexOf(":");e=b.substring(f+1),"confirm"==e&&(e="ok"),"failed"==e&&(e="fail"),-1!=e.indexOf("failed_")&&(e=e.substring(7)),-1!=e.indexOf("fail_")&&(e=e.substring(5)),e=e.replace(/_/g," "),e=e.toLowerCase(),("access denied"==e||"no permission to execute"==e)&&(e="permission denied"),"config"==c&&"function not exist"==e&&(e="ok"),""==e&&(e="fail")}return b=c+":"+e}function j(a){if(a){for(var b=0,c=a.length;c>b;++b){var d=a[b],e=p[d];e&&(a[b]=e)}return a}}function k(a,b){if(!(!D.debug||b&&b.isInnerInvoke)){var c=q[a];c&&(a=c),b&&b._complete&&delete b._complete,console.log('"'+a+'",',b||"")}}function l(a){if(!(v||w||D.debug||"6.0.2">A||C.systemType<0)){var b=new Image;C.appId=D.appId,C.initTime=B.initEndTime-B.initStartTime,C.preVerifyTime=B.preVerifyEndTime-B.preVerifyStartTime,I.getNetworkType({isInnerInvoke:!0,success:function(a){C.networkType=a.networkType;var c="https://open.weixin.qq.com/sdk/report?v="+C.version+"&o="+C.isPreVerifyOk+"&s="+C.systemType+"&c="+C.clientVersion+"&a="+C.appId+"&n="+C.networkType+"&i="+C.initTime+"&p="+C.preVerifyTime+"&u="+C.url;b.src=c}})}}function m(){return(new Date).getTime()}function n(b){x&&(a.WeixinJSBridge?b():r.addEventListener&&r.addEventListener("WeixinJSBridgeReady",b,!1))}function o(){I.invoke||(I.invoke=function(b,c,d){a.WeixinJSBridge&&WeixinJSBridge.invoke(b,e(c),d)},I.on=function(b,c){a.WeixinJSBridge&&WeixinJSBridge.on(b,c)})}if(!a.jWeixin){var p={config:"preVerifyJSAPI",onMenuShareTimeline:"menu:share:timeline",onMenuShareAppMessage:"menu:share:appmessage",onMenuShareQQ:"menu:share:qq",onMenuShareWeibo:"menu:share:weiboApp",onMenuShareQZone:"menu:share:QZone",previewImage:"imagePreview",getLocation:"geoLocation",openProductSpecificView:"openProductViewWithPid",addCard:"batchAddCard",openCard:"batchViewCard",chooseWXPay:"getBrandWCPayRequest",openEnterpriseRedPacket:"getRecevieBizHongBaoRequest",startSearchBeacons:"startMonitoringBeacons",stopSearchBeacons:"stopMonitoringBeacons",onSearchBeacons:"onBeaconsInRange",consumeAndShareCard:"consumedShareCard",openAddress:"editAddress"},q=function(){var a={};for(var b in p)a[p[b]]=b;return a}(),r=a.document,s=r.title,t=navigator.userAgent.toLowerCase(),u=navigator.platform.toLowerCase(),v=!(!u.match("mac")&&!u.match("win")),w=-1!=t.indexOf("wxdebugger"),x=-1!=t.indexOf("micromessenger"),y=-1!=t.indexOf("android"),z=-1!=t.indexOf("iphone")||-1!=t.indexOf("ipad"),A=function(){var a=t.match(/micromessenger\/(\d+\.\d+\.\d+)/)||t.match(/micromessenger\/(\d+\.\d+)/);return a?a[1]:""}(),B={initStartTime:m(),initEndTime:0,preVerifyStartTime:0,preVerifyEndTime:0},C={version:1,appId:"",initTime:0,preVerifyTime:0,networkType:"",isPreVerifyOk:1,systemType:z?1:y?2:-1,clientVersion:A,url:encodeURIComponent(location.href)},D={},E={_completes:[]},F={state:0,data:{}};n(function(){B.initEndTime=m()});var G=!1,H=[],I={config:function(a){D=a,k("config",a);var b=D.check===!1?!1:!0;n(function(){if(b)c(p.config,{verifyJsApiList:j(D.jsApiList)},function(){E._complete=function(a){B.preVerifyEndTime=m(),F.state=1,F.data=a},E.success=function(a){C.isPreVerifyOk=0},E.fail=function(a){E._fail?E._fail(a):F.state=-1};var a=E._completes;return a.push(function(){l()}),E.complete=function(b){for(var c=0,d=a.length;d>c;++c)a[c]();E._completes=[]},E}()),B.preVerifyStartTime=m();else{F.state=1;for(var a=E._completes,d=0,e=a.length;e>d;++d)a[d]();E._completes=[]}}),D.beta&&o()},ready:function(a){0!=F.state?a():(E._completes.push(a),!x&&D.debug&&a())},error:function(a){"6.0.2">A||(-1==F.state?a(F.data):E._fail=a)},checkJsApi:function(a){var b=function(a){var b=a.checkResult;for(var c in b){var d=q[c];d&&(b[d]=b[c],delete b[c])}return a};c("checkJsApi",{jsApiList:j(a.jsApiList)},function(){return a._complete=function(a){if(y){var c=a.checkResult;c&&(a.checkResult=JSON.parse(c))}a=b(a)},a}())},onMenuShareTimeline:function(a){d(p.onMenuShareTimeline,{complete:function(){c("shareTimeline",{title:a.title||s,desc:a.title||s,img_url:a.imgUrl||"",link:a.link||location.href,type:a.type||"link",data_url:a.dataUrl||""},a)}},a)},onMenuShareAppMessage:function(a){d(p.onMenuShareAppMessage,{complete:function(){c("sendAppMessage",{title:a.title||s,desc:a.desc||"",link:a.link||location.href,img_url:a.imgUrl||"",type:a.type||"link",data_url:a.dataUrl||""},a)}},a)},onMenuShareQQ:function(a){d(p.onMenuShareQQ,{complete:function(){c("shareQQ",{title:a.title||s,desc:a.desc||"",img_url:a.imgUrl||"",link:a.link||location.href},a)}},a)},onMenuShareWeibo:function(a){d(p.onMenuShareWeibo,{complete:function(){c("shareWeiboApp",{title:a.title||s,desc:a.desc||"",img_url:a.imgUrl||"",link:a.link||location.href},a)}},a)},onMenuShareQZone:function(a){d(p.onMenuShareQZone,{complete:function(){c("shareQZone",{title:a.title||s,desc:a.desc||"",img_url:a.imgUrl||"",link:a.link||location.href},a)}},a)},startRecord:function(a){c("startRecord",{},a)},stopRecord:function(a){c("stopRecord",{},a)},onVoiceRecordEnd:function(a){d("onVoiceRecordEnd",a)},playVoice:function(a){c("playVoice",{localId:a.localId},a)},pauseVoice:function(a){c("pauseVoice",{localId:a.localId},a)},stopVoice:function(a){c("stopVoice",{localId:a.localId},a)},onVoicePlayEnd:function(a){d("onVoicePlayEnd",a)},uploadVoice:function(a){c("uploadVoice",{localId:a.localId,isShowProgressTips:0==a.isShowProgressTips?0:1},a)},downloadVoice:function(a){c("downloadVoice",{serverId:a.serverId,isShowProgressTips:0==a.isShowProgressTips?0:1},a)},translateVoice:function(a){c("translateVoice",{localId:a.localId,isShowProgressTips:0==a.isShowProgressTips?0:1},a)},chooseImage:function(a){c("chooseImage",{scene:"1|2",count:a.count||9,sizeType:a.sizeType||["original","compressed"],sourceType:a.sourceType||["album","camera"]},function(){return a._complete=function(a){if(y){var b=a.localIds;b&&(a.localIds=JSON.parse(b))}},a}())},getLocation:function(a){},previewImage:function(a){c(p.previewImage,{current:a.current,urls:a.urls},a)},uploadImage:function(a){c("uploadImage",{localId:a.localId,isShowProgressTips:0==a.isShowProgressTips?0:1},a)},downloadImage:function(a){c("downloadImage",{serverId:a.serverId,isShowProgressTips:0==a.isShowProgressTips?0:1},a)},getLocalImgData:function(a){G===!1?(G=!0,c("getLocalImgData",{localId:a.localId},function(){return a._complete=function(a){if(G=!1,H.length>0){var b=H.shift();wx.getLocalImgData(b)}},a}())):H.push(a)},getNetworkType:function(a){var b=function(a){var b=a.errMsg;a.errMsg="getNetworkType:ok";var c=a.subtype;if(delete a.subtype,c)a.networkType=c;else{var d=b.indexOf(":"),e=b.substring(d+1);switch(e){case"wifi":case"edge":case"wwan":a.networkType=e;break;default:a.errMsg="getNetworkType:fail"}}return a};c("getNetworkType",{},function(){return a._complete=function(a){a=b(a)},a}())},openLocation:function(a){c("openLocation",{latitude:a.latitude,longitude:a.longitude,name:a.name||"",address:a.address||"",scale:a.scale||28,infoUrl:a.infoUrl||""},a)},getLocation:function(a){a=a||{},c(p.getLocation,{type:a.type||"wgs84"},function(){return a._complete=function(a){delete a.type},a}())},hideOptionMenu:function(a){c("hideOptionMenu",{},a)},showOptionMenu:function(a){c("showOptionMenu",{},a)},closeWindow:function(a){a=a||{},c("closeWindow",{},a)},hideMenuItems:function(a){c("hideMenuItems",{menuList:a.menuList},a)},showMenuItems:function(a){c("showMenuItems",{menuList:a.menuList},a)},hideAllNonBaseMenuItem:function(a){c("hideAllNonBaseMenuItem",{},a)},showAllNonBaseMenuItem:function(a){c("showAllNonBaseMenuItem",{},a)},scanQRCode:function(a){a=a||{},c("scanQRCode",{needResult:a.needResult||0,scanType:a.scanType||["qrCode","barCode"]},function(){return a._complete=function(a){if(z){var b=a.resultStr;if(b){var c=JSON.parse(b);a.resultStr=c&&c.scan_code&&c.scan_code.scan_result}}},a}())},openAddress:function(a){c(p.openAddress,{},function(){return a._complete=function(a){a=g(a)},a}())},openProductSpecificView:function(a){c(p.openProductSpecificView,{pid:a.productId,view_type:a.viewType||0,ext_info:a.extInfo},a)},addCard:function(a){for(var b=a.cardList,d=[],e=0,f=b.length;f>e;++e){var g=b[e],h={card_id:g.cardId,card_ext:g.cardExt};d.push(h)}c(p.addCard,{card_list:d},function(){return a._complete=function(a){var b=a.card_list;if(b){b=JSON.parse(b);for(var c=0,d=b.length;d>c;++c){var e=b[c];e.cardId=e.card_id,e.cardExt=e.card_ext,e.isSuccess=e.is_succ?!0:!1,delete e.card_id,delete e.card_ext,delete e.is_succ}a.cardList=b,delete a.card_list}},a}())},chooseCard:function(a){c("chooseCard",{app_id:D.appId,location_id:a.shopId||"",sign_type:a.signType||"SHA1",card_id:a.cardId||"",card_type:a.cardType||"",card_sign:a.cardSign,time_stamp:a.timestamp+"",nonce_str:a.nonceStr},function(){return a._complete=function(a){a.cardList=a.choose_card_info,delete a.choose_card_info},a}())},openCard:function(a){for(var b=a.cardList,d=[],e=0,f=b.length;f>e;++e){var g=b[e],h={card_id:g.cardId,code:g.code};d.push(h)}c(p.openCard,{card_list:d},a)},consumeAndShareCard:function(a){c(p.consumeAndShareCard,{consumedCardId:a.cardId,consumedCode:a.code},a)},chooseWXPay:function(a){c(p.chooseWXPay,f(a),a)},openEnterpriseRedPacket:function(a){c(p.openEnterpriseRedPacket,f(a),a)},startSearchBeacons:function(a){c(p.startSearchBeacons,{ticket:a.ticket},a)},stopSearchBeacons:function(a){c(p.stopSearchBeacons,{},a)},onSearchBeacons:function(a){d(p.onSearchBeacons,a)},openEnterpriseChat:function(a){c("openEnterpriseChat",{useridlist:a.userIds,chatname:a.groupName},a)}},J=1,K={};return r.addEventListener("error",function(a){if(!y){var b=a.target,c=b.tagName,d=b.src;if("IMG"==c||"VIDEO"==c||"AUDIO"==c||"SOURCE"==c){var e=-1!=d.indexOf("wxlocalresource://");if(e){a.preventDefault(),a.stopPropagation();var f=b["wx-id"];if(f||(f=J++,b["wx-id"]=f),K[f])return;K[f]=!0,wx.ready(function(){wx.getLocalImgData({localId:d,success:function(a){b.src=a.localData}})})}}}},!0),r.addEventListener("load",function(a){if(!y){var b=a.target,c=b.tagName;b.src;if("IMG"==c||"VIDEO"==c||"AUDIO"==c||"SOURCE"==c){var d=b["wx-id"];d&&(K[d]=!1)}}},!0),b&&(a.wx=a.jWeixin=I),I}});

(function (global) {
    global.mapleWx = mapleWx(global.wx);
    var margin = function(o,n){
        for (var p in n){
            if(n.hasOwnProperty(p))
                o[p]=n[p];
        }
        return o;
    };
    function mapleWx(wx) {
        'use strict';
        var mapleApi = new _mapleApi();
        var jsApiList = ['onMenuShareTimeline', 'onMenuShareAppMessage', 'onMenuShareQQ', 'onMenuShareWeibo', 'onMenuShareQZone', 'startRecord', 'stopRecord', 'onVoiceRecordEnd', 'playVoice', 'pauseVoice', 'stopVoice', 'onVoicePlayEnd', 'uploadVoice', 'downloadVoice', 'chooseImage', 'previewImage', 'uploadImage', 'downloadImage', 'translateVoice', 'getNetworkType', 'openLocation', 'getLocation', 'hideOptionMenu', 'showOptionMenu', 'hideMenuItems', 'showMenuItems', 'hideAllNonBaseMenuItem', 'showAllNonBaseMenuItem', 'closeWindow', 'scanQRCode', 'chooseWXPay', 'openProductSpecificView', 'addCard', 'chooseCard', 'openCard'];
        function _mapleApi() {
            var that = this;
            //微信接口初始化
            this.init = function (config, readFn, errorFn) {
                mapleApi.option.config = config;
                mapleApi.option.wx = wx;
                wx.config({
                    debug: config.debug || false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
                    appId: config.appId, // 必填，公众号的唯一标识
                    timestamp: config.timestamp, // 必填，生成签名的时间戳
                    nonceStr: config.nonceStr, // 必填，生成签名的随机串
                    signature: config.signature,// 必填，签名，见附录1
                    jsApiList: config.jsApiList || jsApiList // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
                });
                wx.ready(function () {
                    readFn && readFn.call(mapleApi);
                });
                wx.error(function (error) {
                    errorFn && errorFn.call(mapleApi, error);
                });
                return mapleApi;
            };

            //隐藏不安全接口
            that.hideNonSafetyMenuItem = function () {
                var list = ['menuItem:copyUrl', 'menuItem:delete', '', 'menuItem:originPage', 'menuItem:openWithQQBrowser', 'menuItem:openWithSafari', 'menuItem:share:email', 'menuItem:share:brand', 'menuItem:delete', 'menuItem:editTag'];
                that.hideMenuItems(list);
            };
            //一键配置所有分享
            that.onMenuShareAll = function(options,successFn,closeFn){
                that.onMenuShareAppMessage(options,function(){
                    successFn && successFn('AppMessage');
                },function(){
                    closeFn && closeFn('AppMessage');
                });
                that.onMenuShareQQ(options,function(){
                    successFn && successFn('QQ');
                },function(){
                    closeFn && closeFn('QQ');
                });
                that.onMenuShareQZone(options,function(){
                    successFn && successFn('QZone');
                },function(){
                    closeFn && closeFn('QZone');
                });
                that.onMenuShareTimeline(options,function(){
                    successFn && successFn('Timeline');
                },function(){
                    closeFn && closeFn('Timeline');
                });
                that.onMenuShareWeibo(options,function(){
                    successFn && successFn('Weibo');
                },function(){
                    closeFn && closeFn('Weibo');
                });
            };
        };
        //拍照或从手机相册中选图接口
        _mapleApi.prototype.chooseImage = function (options, successFn) {
            options || (options = {});
            if (typeof(options) == 'function') {
                successFn = options;
                options = {};
            }
            wx.chooseImage({
                count: options.count || 1, // 默认9
                sizeType: options.sizeType || ['original', 'compressed'], // 可以指定是原图还是压缩图，默认二者都有
                sourceType: options.sourceType || ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有
                success: function (res) {
                    var localIds = res.localIds; // 返回选定照片的本地ID列表，localId可以作为img标签的src属性显示图片
                    successFn && successFn.call(mapleApi, localIds, res);
                },
                fail:function(err){
                }
            });
        };
        //预览图片接口
        _mapleApi.prototype.previewImage = function (current, urls) {
            wx.previewImage({
                current: current, // 当前显示图片的http链接
                urls: urls || [] // 需要预览的图片http链接列表
            });
        };
        //获取本地图片接口
        _mapleApi.prototype.getLocalImgData = function (localId, successFn) {
            wx.getLocalImgData({
                localId: localId, // 图片的localID
                success: function (res) {
                    var localData = res.localData; // localData是图片的base64数据，可以用img标签显示
                    successFn && successFn.call(mapleApi, localIds, res);
                }
            });
        };
        //上传图片接口
        _mapleApi.prototype.uploadImageOne = function (localId, successFn, isShowProgressTips) {
            wx.uploadImage({
                localId: localId, // 需要上传的图片的本地ID，由chooseImage接口获得
                isShowProgressTips: isShowProgressTips || 1, // 默认为1，显示进度提示
                success: function (res) {
                    var serverId = res.serverId; // 返回图片的服务器端ID
                    successFn && successFn.call(mapleApi, serverId, res);
                }
            });
        };
        //上传多张图片接口
        _mapleApi.prototype.uploadImage = function (localIds, successFn, errorFn) {
            // var _this = this,allFn=[];
            // localIds.forEach(function(localId,k){
            //     allFn.push(new Promise(function(resolve){
            //         _this.uploadImageOne(localId,function(serverId){
            //             return resolve(serverId);
            //         })
            //     }));
            // });
            // Promise.all(allFn).then(function(){
            //     var i = arguments.length,serverIdList = new Array(i);
            //     while(i--){serverIdList[i] = arguments[i];}
            //     successFn && successFn.call(mapleApi,serverIdList[0]);
            // }).catch(function(err){
            //     errorFn && errorFn.call(mapleApi,err,localIds);
            // });
            var serverIdList = [], length = localIds.length, _this = this;
            var _upload = function () {
                var localId = localIds[--length];
                if (!localId) return errorFn && errorFn.call(mapleApi, localIds, serverIdList);
                _this.uploadImageOne(localId, function (serverId) {
                    serverIdList.push(serverId);
                    length==0 ? successFn.call(mapleApi, serverIdList) : _upload();
                })
            };
            _upload();


        };
        //下载图片接口
        _mapleApi.prototype.downloadImage = function (serverId, successFn, isShowProgressTips) {
            wx.downloadImage({
                serverId: serverId, // 需要下载的图片的服务器端ID，由uploadImage接口获得
                isShowProgressTips: isShowProgressTips || 1, // 默认为1，显示进度提示
                success: function (res) {
                    var localId = res.localId; // 返回图片下载后的本地ID
                    successFn && successFn.call(mapleApi, localId);
                }
            });
        };


        //开始录音接口
        _mapleApi.prototype.startRecord = function () {
            wx.startRecord.call(mapleApi);
        };
        //停止录音接口
        _mapleApi.prototype.stopRecord = function (successFn) {
            wx.stopRecord({
                success: function (res) {
                    var localId = res.localId;
                    successFn && successFn.call(mapleApi, localId, res);
                }
            });
        };
        //监听录音自动停止接口
        _mapleApi.prototype.onVoiceRecordEnd = function (completeFn) {
            wx.onVoiceRecordEnd({
                // 录音时间超过一分钟没有停止的时候会执行 complete 回调
                complete: function (res) {
                    var localId = res.localId;
                    completeFn && completeFn.call(mapleApi, localId, res);
                }
            });
        };
        //播放语音接口
        _mapleApi.prototype.playVoice = function (localId) {
            wx.playVoice({
                localId: localId // 需要播放的音频的本地ID，由stopRecord接口获得
            });
        };
        //暂停播放接口
        _mapleApi.prototype.pauseVoice = function (localId) {
            wx.pauseVoice({
                localId: localId // 需要暂停的音频的本地ID，由stopRecord接口获得
            });
        };
        //停止播放接口
        _mapleApi.prototype.stopVoice = function (localId) {
            wx.stopVoice({
                localId: localId // 需要停止的音频的本地ID，由stopRecord接口获得
            });
        };
        //监听语音播放完毕接口
        _mapleApi.prototype.onVoicePlayEnd = function (successFn) {
            wx.onVoicePlayEnd({
                success: function (res) {
                    var localId = res.localId; // 返回音频的本地ID
                    successFn && successFn.call(mapleApi, localId, res);
                }
            });
        };
        //上传语音接口
        _mapleApi.prototype.uploadVoice = function (localId, successFn, isShowProgressTips) {
            wx.uploadVoice({
                localId: localId, // 需要上传的音频的本地ID，由stopRecord接口获得
                isShowProgressTips: isShowProgressTips || 1, // 默认为1，显示进度提示
                success: function (res) {
                    var serverId = res.serverId; // 返回音频的服务器端ID
                    successFn && successFn.call(mapleApi, serverId, res);
                }
            });
        };
        //下载语音接口
        _mapleApi.prototype.downloadVoice = function (serverId, successFn, isShowProgressTips) {
            wx.downloadVoice({
                serverId: serverId, // 需要下载的音频的服务器端ID，由uploadVoice接口获得
                isShowProgressTips: isShowProgressTips || 1, // 默认为1，显示进度提示
                success: function (res) {
                    var localId = res.localId; // 返回音频的本地ID
                    successFn && successFn.call(mapleApi, localId, res);
                }
            });
        };
        //识别音频并返回识别结果接口
        _mapleApi.prototype.translateVoice = function (localId, successFn, isShowProgressTips) {
            wx.translateVoice({
                localId: localId, // 需要识别的音频的本地Id，由录音相关接口获得
                isShowProgressTips: isShowProgressTips || 1, // 默认为1，显示进度提示
                success: function (res) {
                    successFn && successFn.call(mapleApi, res.translateResult, res);
                }
            });
        };

        //获取网络状态接口
        _mapleApi.prototype.getNetworkType = function (successFn) {
            wx.getNetworkType({
                success: function (res) {
                    successFn && successFn.call(mapleApi, res.networkType, res);
                }
            });
        };
        //使用微信内置地图查看位置接口
        _mapleApi.prototype.openLocation = function (options) {
            wx.openLocation({
                latitude: options.latitude || 0, // 纬度，浮点数，范围为90 ~ -90
                longitude: options.longitude || 0, // 经度，浮点数，范围为180 ~ -180。
                name: options.name || '', // 位置名
                address: options.address || '', // 地址详情说明
                scale: options.scale || 14, // 地图缩放级别,整形值,范围从1~28。默认为最大
                infoUrl: options.infoUrl || '' // 在查看位置界面底部显示的超链接,可点击跳转
            });
        };
        //获取地理位置接口
        _mapleApi.prototype.getLocation = function (successFn, type) {
            wx.getLocation({
                type: type || 'wgs84', // 默认为wgs84的gps坐标，如果要返回直接给openLocation用的火星坐标，可传入'gcj02'
                success: function (res) {
                    var latitude = res.latitude; // 纬度，浮点数，范围为90 ~ -90
                    var longitude = res.longitude; // 经度，浮点数，范围为180 ~ -180。
//                        var speed = res.speed; // 速度，以米/每秒计
//                        var accuracy = res.accuracy; // 位置精度
                    successFn && successFn.call(mapleApi, latitude, longitude, res);
                }
            });
        };
        //开启查找周边ibeacon设备接口
        _mapleApi.prototype.startSearchBeacons = function (completeFn, ticket) {
            wx.startSearchBeacons({
                ticket: ticket || "",  //摇周边的业务ticket, 系统自动添加在摇出来的页面链接后面
                complete: function (argv) {
                    //开启查找完成后的回调函数
                    completeFn && completeFn.call(mapleApi, argv);
                }
            });
        };
        //关闭查找周边ibeacon设备接口
        _mapleApi.prototype.stopSearchBeacons = function (completeFn) {
            wx.stopSearchBeacons({
                complete: function (res) {
                    //关闭查找完成后的回调函数
                    completeFn && completeFn.call(mapleApi, res);
                }
            });
        };
        //监听周边ibeacon设备接口
        _mapleApi.prototype.onSearchBeacons = function (completeFn) {
            wx.onSearchBeacons({
                complete: function (argv) {
                    //回调函数，可以数组形式取得该商家注册的在周边的相关设备列表
                    completeFn && completeFn.call(mapleApi, argv);
                }
            });
        };
        //关闭当前网页窗口接口
        _mapleApi.prototype.closeWindow = function () {
            wx.closeWindow();
        };
        //批量隐藏功能按钮接口
        _mapleApi.prototype.hideMenuItems = function (menuList) {
            wx.hideMenuItems({
                menuList: menuList || [] // 要隐藏的菜单项，只能隐藏“传播类”和“保护类”按钮，所有menu项见附录3
            });
        };
        //批量显示功能按钮接口
        _mapleApi.prototype.showMenuItems = function (menuList) {
            wx.showMenuItems({
                menuList: menuList || [] // 要显示的菜单项，所有menu项见附录3
            });
        };
        //隐藏所有非基础按钮接口
        _mapleApi.prototype.hideAllNonBaseMenuItem = function () {
            wx.hideAllNonBaseMenuItem();
        };
        //显示所有功能按钮接口
        _mapleApi.prototype.showAllNonBaseMenuItem = function () {
            wx.showAllNonBaseMenuItem();
        };
        //调起微信扫一扫接口
        _mapleApi.prototype.scanQRCode = function (options, successFn) {
            options || (options = {});
            if (typeof(options) == 'function') {
                successFn = options;
                options = {};
            }
            wx.scanQRCode({
                needResult: options.needResult || 0, // 默认为0，扫描结果由微信处理，1则直接返回扫描结果，
                scanType: options.scanType || ["qrCode", "barCode"], // 可以指定扫二维码还是一维码，默认二者都有
                success: function (res) {
                    var result = res.resultStr; // 当needResult 为 1 时，扫码返回的结果
                    successFn && successFn.call(mapleApi, result, res);
                }
            });
        };
        //跳转微信商品页接口
        _mapleApi.prototype.openProductSpecificView = function (productId, viewType) {
            wx.openProductSpecificView({
                productId: productId, // 商品id
                viewType: viewType || 0 // 0.默认值，普通商品详情页1.扫一扫商品详情页2.小店商品详情页
            });
        };
        //拉取适用卡券列表并获取用户选择信息
        _mapleApi.prototype.chooseCard = function (options, successFn) {
            wx.chooseCard({
                shopId: options.shopId, // 门店Id
                cardType: options.cardType, // 卡券类型
                cardId: options.cardId, // 卡券Id
                timestamp: options.timestamp, // 卡券签名时间戳
                nonceStr: options.nonceStr, // 卡券签名随机串
                signType: options.signType || 'SHA1', // 签名方式，默认'SHA1'
                cardSign: options.cardSign, // 卡券签名
                success: function (res) {
                    var cardList = res.cardList; // 用户选中的卡券列表信息
                    successFn && successFn.call(mapleApi, cardList, res);
                }
            });
        };
        //批量添加卡券接口
        _mapleApi.prototype.addCard = function (cardList, successFn) {
            wx.addCard({
                cardList: cardList, // 需要添加的卡券列表
                success: function (res) {
                    var cardList = res.cardList; // 添加的卡券列表信息
                    successFn && successFn.call(mapleApi, cardList, res);
                }
            });
        };
        //查看微信卡包中的卡券接口
        _mapleApi.prototype.openCard = function (cardList) {
            wx.openCard({
                cardList: cardList// 需要打开的卡券列表
            });
        };
        //发起一个微信支付请求
        _mapleApi.prototype.chooseWXPay = function (config, successFn,groupFn) {
            groupFn || (groupFn = {});

            margin(groupFn,{
                timestamp: parseInt(config.timestamp), // 支付签名时间戳，注意微信jssdk中的所有使用timestamp字段均为小写。但最新版的支付后台生成签名使用的timeStamp字段名需大写其中的S字符
                nonceStr: config.nonceStr, // 支付签名随机串，不长于 32 位
                package: config.package, // 统一支付接口返回的prepay_id参数值，提交格式如：prepay_id=***）
                signType: config.signType || 'SHA1', // 签名方式，默认为'SHA1'，使用新版支付需传入'MD5'
                paySign: config.paySign, // 支付签名
                success: function (res) {
                    // 支付成功后的回调函数
                    successFn && successFn.call(mapleApi, res);
                }
            });
            wx.chooseWXPay(groupFn);
        };
        //获取“分享到朋友圈”按钮点击状态及自定义分享内容接口
        _mapleApi.prototype.onMenuShareTimeline = function (options, successFn, cancelFn) {
            options || (options = {});
            wx.onMenuShareTimeline({
                title: options.title || '', // 分享标题
                link: options.link || location.href, // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                imgUrl: options.imgUrl || '', // 分享图标
                success: function () {
                    // 用户确认分享后执行的回调函数
                    successFn && successFn.call(mapleApi);
                },
                cancel: function () {
                    // 用户取消分享后执行的回调函数
                    cancelFn && cancelFn.call(mapleApi);
                }
            });
        };
        //获取“分享给朋友”按钮点击状态及自定义分享内容接口
        _mapleApi.prototype.onMenuShareAppMessage = function (options, successFn, cancelFn) {
            options || (options = {});
            wx.onMenuShareAppMessage({
                title: options.title || '', // 分享标题
                desc: options.desc || '', // 分享描述
                imgUrl: options.imgUrl || '', // 分享图标
                link: options.link || location.href, // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                type: options.type || 'link', // 分享类型,music、video或link，不填默认为link
                dataUrl: options.dataUrl || '', // 如果type是music或video，则要提供数据链接，默认为空
                success: function () {
                    // 用户确认分享后执行的回调函数
                    successFn && successFn.call(mapleApi);
                },
                cancel: function () {
                    // 用户取消分享后执行的回调函数
                    cancelFn && cancelFn.call(mapleApi);
                }
            });
        };
        //获取“分享到QQ”按钮点击状态及自定义分享内容接口
        _mapleApi.prototype.onMenuShareQQ = function (options, successFn, cancelFn) {
            options || (options = {});
            wx.onMenuShareQQ({
                title: options.title || '', // 分享标题
                desc: options.desc || '', // 分享描述
                link: options.link || location.href, // 分享链接
                imgUrl: options.imgUrl || '', // 分享图标
                success: function () {
                    // 用户确认分享后执行的回调函数
                    successFn && successFn.call(mapleApi);
                },
                cancel: function () {
                    // 用户取消分享后执行的回调函数
                    cancelFn && cancelFn.call(mapleApi);
                }
            });
        };
        //获取“分享到腾讯微博”按钮点击状态及自定义分享内容接口
        _mapleApi.prototype.onMenuShareWeibo = function (options, successFn, cancelFn) {
            options || (options = {});

            wx.onMenuShareWeibo({
                title: options.title || '', // 分享标题
                desc: options.imgUrl || '', // 分享描述
                link: options.imgUrl || location.href, // 分享链接
                imgUrl: options.imgUrl || '', // 分享图标
                success: function () {
                    // 用户确认分享后执行的回调函数
                    successFn && successFn.call(mapleApi);
                },
                cancel: function () {
                    // 用户取消分享后执行的回调函数
                    cancelFn && cancelFn.call(mapleApi);
                }
            });
        };
        //获取“分享到QQ空间”按钮点击状态及自定义分享内容接口
        _mapleApi.prototype.onMenuShareQZone = function (options, successFn, cancelFn) {
            options || (options = {});
            wx.onMenuShareQZone({
                title: options.title || '', // 分享标题
                desc: options.desc || '', // 分享描述
                link: options.link || location.href, // 分享链接
                imgUrl: options.imgUrl || '', // 分享图标
                success: function () {
                    // 用户确认分享后执行的回调函数
                    successFn && successFn.call(mapleApi);
                },
                cancel: function () {
                    // 用户取消分享后执行的回调函数
                    cancelFn && cancelFn.call(mapleApi);
                }
            });
        };
        _mapleApi.prototype.option = {};

        return mapleApi.init;
    }
}(this));