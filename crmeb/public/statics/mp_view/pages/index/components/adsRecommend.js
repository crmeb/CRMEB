(global["webpackJsonp"]=global["webpackJsonp"]||[]).push([["pages/index/components/adsRecommend"],{4668:function(n,t,e){},"59f6":function(n,t,e){"use strict";var a;e.d(t,"b",(function(){return i})),e.d(t,"c",(function(){return o})),e.d(t,"a",(function(){return a}));var i=function(){var n=this,t=n.$createElement;n._self._c},o=[]},"9e76":function(n,t,e){"use strict";var a=e("4668"),i=e.n(a);i.a},ae78:function(n,t,e){"use strict";(function(n){Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var a=e("4729"),i=getApp(),o={name:"adsRecommend",props:{dataConfig:{type:Object,default:function(){}}},watch:{dataConfig:{immediate:!0,handler:function(n,t){n&&(this.recommendList=n.imgList.list,this.isShow=n.isShow.val)}}},data:function(){return{recommendList:[],name:this.$options.name,isIframe:i.globalData.isIframe,isShow:!0}},created:function(){},mounted:function(){},methods:{goDetail:function(t){(0,a.goPage)(t).then((function(e){n.navigateTo({url:t.info[0].value})}))}}};t.default=o}).call(this,e("543d")["default"])},b781:function(n,t,e){"use strict";e.r(t);var a=e("ae78"),i=e.n(a);for(var o in a)"default"!==o&&function(n){e.d(t,n,(function(){return a[n]}))}(o);t["default"]=i.a},e1ad:function(n,t,e){"use strict";e.r(t);var a=e("59f6"),i=e("b781");for(var o in i)"default"!==o&&function(n){e.d(t,n,(function(){return i[n]}))}(o);e("9e76");var u,c=e("f0c5"),f=Object(c["a"])(i["default"],a["b"],a["c"],!1,null,null,null,!1,a["a"],u);t["default"]=f.exports}}]);
;(global["webpackJsonp"] = global["webpackJsonp"] || []).push([
    'pages/index/components/adsRecommend-create-component',
    {
        'pages/index/components/adsRecommend-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('543d')['createComponent'](__webpack_require__("e1ad"))
        })
    },
    [['pages/index/components/adsRecommend-create-component']]
]);
