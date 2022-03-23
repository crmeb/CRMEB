(global["webpackJsonp"]=global["webpackJsonp"]||[]).push([["components/kefuIcon/index"],{3849:function(t,n,e){"use strict";e.r(n);var u=e("d8d6"),o=e("afc6");for(var c in o)"default"!==c&&function(t){e.d(n,t,(function(){return o[t]}))}(c);e("a462");var f,r=e("f0c5"),a=Object(r["a"])(o["default"],u["b"],u["c"],!1,null,null,null,!1,u["a"],f);n["default"]=a.exports},"8b8f":function(t,n,e){"use strict";Object.defineProperty(n,"__esModule",{value:!0}),n.default=void 0;var u=e("72cd"),o=(getApp(),{name:"kefuIcon",props:{ids:{type:Number,default:0},routineContact:{type:Number,default:0},storeInfo:{type:Object,default:function(){}},goodsCon:{type:Number,default:0}},data:function(){return{top:"480"}},mounted:function(){},methods:{goCustomer:function(){(0,u.getCustomer)("/pages/customer_list/chat?productId=".concat(this.ids))},setTouchMove:function(t){var n=this;t.touches[0].clientY<480&&t.touches[0].clientY>66&&(n.top=t.touches[0].clientY)}},created:function(){}});n.default=o},a462:function(t,n,e){"use strict";var u=e("f7f4"),o=e.n(u);o.a},afc6:function(t,n,e){"use strict";e.r(n);var u=e("8b8f"),o=e.n(u);for(var c in u)"default"!==c&&function(t){e.d(n,t,(function(){return u[t]}))}(c);n["default"]=o.a},d8d6:function(t,n,e){"use strict";var u;e.d(n,"b",(function(){return o})),e.d(n,"c",(function(){return c})),e.d(n,"a",(function(){return u}));var o=function(){var t=this,n=t.$createElement;t._self._c},c=[]},f7f4:function(t,n,e){}}]);
;(global["webpackJsonp"] = global["webpackJsonp"] || []).push([
    'components/kefuIcon/index-create-component',
    {
        'components/kefuIcon/index-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('543d')['createComponent'](__webpack_require__("3849"))
        })
    },
    [['components/kefuIcon/index-create-component']]
]);
