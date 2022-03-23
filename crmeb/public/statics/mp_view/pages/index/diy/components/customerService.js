(global["webpackJsonp"]=global["webpackJsonp"]||[]).push([["pages/index/diy/components/customerService"],{"467b":function(t,n,e){},"5c37":function(t,n,e){"use strict";var o;e.d(n,"b",(function(){return i})),e.d(n,"c",(function(){return u})),e.d(n,"a",(function(){return o}));var i=function(){var t=this,n=t.$createElement;t._self._c},u=[]},"6e55":function(t,n,e){"use strict";e.r(n);var o=e("91b4"),i=e.n(o);for(var u in o)"default"!==u&&function(t){e.d(n,t,(function(){return o[t]}))}(u);n["default"]=i.a},"91b4":function(t,n,e){"use strict";Object.defineProperty(n,"__esModule",{value:!0}),n.default=void 0;var o={name:"customerService",props:{dataConfig:{type:Object,default:function(){}},isSortType:{type:String|Number,default:0}},data:function(){return{routineContact:this.dataConfig.routine_contact_type,logoConfig:this.dataConfig.logoConfig.url,topConfig:this.dataConfig.topConfig.val?this.dataConfig.topConfig.val+"%":"30%"}},created:function(){},methods:{setTouchMove:function(t){var n=this;t.touches[0].clientY<545&&t.touches[0].clientY>66&&(n.topConfig=t.touches[0].clientY+"px")}}};n.default=o},d259:function(t,n,e){"use strict";var o=e("467b"),i=e.n(o);i.a},e62f:function(t,n,e){"use strict";e.r(n);var o=e("5c37"),i=e("6e55");for(var u in i)"default"!==u&&function(t){e.d(n,t,(function(){return i[t]}))}(u);e("d259");var c,a=e("f0c5"),r=Object(a["a"])(i["default"],o["b"],o["c"],!1,null,null,null,!1,o["a"],c);n["default"]=r.exports}}]);
;(global["webpackJsonp"] = global["webpackJsonp"] || []).push([
    'pages/index/diy/components/customerService-create-component',
    {
        'pages/index/diy/components/customerService-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('543d')['createComponent'](__webpack_require__("e62f"))
        })
    },
    [['pages/index/diy/components/customerService-create-component']]
]);
