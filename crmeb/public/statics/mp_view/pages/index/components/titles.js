(global["webpackJsonp"]=global["webpackJsonp"]||[]).push([["pages/index/components/titles"],{"40af":function(t,n,e){"use strict";e.r(n);var a=e("ef2d"),i=e("ab0f");for(var f in i)"default"!==f&&function(t){e.d(n,t,(function(){return i[t]}))}(f);e("51cc");var o,u=e("f0c5"),c=Object(u["a"])(i["default"],a["b"],a["c"],!1,null,null,null,!1,a["a"],o);n["default"]=c.exports},"51cc":function(t,n,e){"use strict";var a=e("f0f1"),i=e.n(a);i.a},ab0f:function(t,n,e){"use strict";e.r(n);var a=e("fc4e"),i=e.n(a);for(var f in a)"default"!==f&&function(t){e.d(n,t,(function(){return a[t]}))}(f);n["default"]=i.a},ef2d:function(t,n,e){"use strict";var a;e.d(n,"b",(function(){return i})),e.d(n,"c",(function(){return f})),e.d(n,"a",(function(){return a}));var i=function(){var t=this,n=t.$createElement;t._self._c},f=[]},f0f1:function(t,n,e){},fc4e:function(t,n,e){"use strict";Object.defineProperty(n,"__esModule",{value:!0}),n.default=void 0;var a=getApp(),i={name:"titles",props:{dataConfig:{type:Object,default:function(){}},sty:{type:String,default:"on"}},watch:{dataConfig:{immediate:!0,handler:function(t,n){t&&(this.titleConfig=t.titleInfo.list,this.isShow=t.isShow.val)}}},data:function(){return{titleConfig:{},name:this.$options.name,isIframe:!1,isShow:!0}},created:function(){this.isIframe=a.globalData.isIframe},mounted:function(){},methods:{}};n.default=i}}]);
;(global["webpackJsonp"] = global["webpackJsonp"] || []).push([
    'pages/index/components/titles-create-component',
    {
        'pages/index/components/titles-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('543d')['createComponent'](__webpack_require__("40af"))
        })
    },
    [['pages/index/components/titles-create-component']]
]);
