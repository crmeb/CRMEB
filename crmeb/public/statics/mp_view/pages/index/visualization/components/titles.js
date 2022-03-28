(global["webpackJsonp"]=global["webpackJsonp"]||[]).push([["pages/index/visualization/components/titles"],{"29cd":function(t,n,e){},"4a94":function(t,n,e){"use strict";e.r(n);var i=e("821f"),a=e("defd");for(var f in a)"default"!==f&&function(t){e.d(n,t,(function(){return a[t]}))}(f);e("f28c");var o,u=e("f0c5"),c=Object(u["a"])(a["default"],i["b"],i["c"],!1,null,null,null,!1,i["a"],o);n["default"]=c.exports},"821f":function(t,n,e){"use strict";var i;e.d(n,"b",(function(){return a})),e.d(n,"c",(function(){return f})),e.d(n,"a",(function(){return i}));var a=function(){var t=this,n=t.$createElement;t._self._c},f=[]},cea2:function(t,n,e){"use strict";Object.defineProperty(n,"__esModule",{value:!0}),n.default=void 0;var i=getApp(),a={name:"titles",props:{dataConfig:{type:Object,default:function(){}},sty:{type:String,default:"on"}},watch:{dataConfig:{immediate:!0,handler:function(t,n){t&&(this.titleConfig=t.titleInfo.list,this.isShow=t.isShow.val)}}},data:function(){return{titleConfig:{},name:this.$options.name,isIframe:!1,isShow:!0}},created:function(){this.isIframe=i.globalData.isIframe},mounted:function(){},methods:{}};n.default=a},defd:function(t,n,e){"use strict";e.r(n);var i=e("cea2"),a=e.n(i);for(var f in i)"default"!==f&&function(t){e.d(n,t,(function(){return i[t]}))}(f);n["default"]=a.a},f28c:function(t,n,e){"use strict";var i=e("29cd"),a=e.n(i);a.a}}]);
;(global["webpackJsonp"] = global["webpackJsonp"] || []).push([
    'pages/index/visualization/components/titles-create-component',
    {
        'pages/index/visualization/components/titles-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('543d')['createComponent'](__webpack_require__("4a94"))
        })
    },
    [['pages/index/visualization/components/titles-create-component']]
]);
