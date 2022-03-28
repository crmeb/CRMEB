(global["webpackJsonp"]=global["webpackJsonp"]||[]).push([["pages/index/diy/components/customerService"],{"0234":function(t,n,e){},"1dca":function(t,n,e){"use strict";e.r(n);var o=e("a5ce"),c=e("a925");for(var a in c)"default"!==a&&function(t){e.d(n,t,(function(){return c[t]}))}(a);e("cb4c");var i,u=e("f0c5"),r=Object(u["a"])(c["default"],o["b"],o["c"],!1,null,null,null,!1,o["a"],i);n["default"]=r.exports},a5ce:function(t,n,e){"use strict";var o;e.d(n,"b",(function(){return c})),e.d(n,"c",(function(){return a})),e.d(n,"a",(function(){return o}));var c=function(){var t=this,n=t.$createElement;t._self._c},a=[]},a925:function(t,n,e){"use strict";e.r(n);var o=e("c827"),c=e.n(o);for(var a in o)"default"!==a&&function(t){e.d(n,t,(function(){return o[t]}))}(a);n["default"]=c.a},c827:function(t,n,e){"use strict";Object.defineProperty(n,"__esModule",{value:!0}),n.default=void 0;var o={name:"customerService",props:{dataConfig:{type:Object,default:function(){}},isSortType:{type:String|Number,default:0}},data:function(){return{routineContact:this.dataConfig.routine_contact_type,logoConfig:this.dataConfig.logoConfig.url,topConfig:this.dataConfig.topConfig.val?this.dataConfig.topConfig.val+"%":"30%"}},created:function(){},methods:{setTouchMove:function(t){var n=this;t.touches[0].clientY<545&&t.touches[0].clientY>66&&(n.topConfig=t.touches[0].clientY+"px")}}};n.default=o},cb4c:function(t,n,e){"use strict";var o=e("0234"),c=e.n(o);c.a}}]);
;(global["webpackJsonp"] = global["webpackJsonp"] || []).push([
    'pages/index/diy/components/customerService-create-component',
    {
        'pages/index/diy/components/customerService-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('543d')['createComponent'](__webpack_require__("1dca"))
        })
    },
    [['pages/index/diy/components/customerService-create-component']]
]);
