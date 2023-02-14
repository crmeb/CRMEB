(global["webpackJsonp"]=global["webpackJsonp"]||[]).push([["components/kefuIcon/index"],{"0c73":function(t,e,n){},"107b":function(t,e,n){"use strict";n.r(e);var u=n("cc18"),c=n("50e9");for(var o in c)"default"!==o&&function(t){n.d(e,t,(function(){return c[t]}))}(o);n("efd0");var r,a=n("f0c5"),f=Object(a["a"])(c["default"],u["b"],u["c"],!1,null,null,null,!1,u["a"],r);e["default"]=f.exports},"50e9":function(t,e,n){"use strict";n.r(e);var u=n("ef09"),c=n.n(u);for(var o in u)"default"!==o&&function(t){n.d(e,t,(function(){return u[t]}))}(o);e["default"]=c.a},cc18:function(t,e,n){"use strict";var u;n.d(e,"b",(function(){return c})),n.d(e,"c",(function(){return o})),n.d(e,"a",(function(){return u}));var c=function(){var t=this,e=t.$createElement;t._self._c},o=[]},ef09:function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var u=n("8aa5"),c=(getApp(),{name:"kefuIcon",props:{ids:{type:Number,default:0},routineContact:{type:Number,default:0},storeInfo:{type:Object,default:function(){}},goodsCon:{type:Number,default:0}},data:function(){return{top:"480"}},mounted:function(){},methods:{goCustomer:function(){(0,u.getCustomer)("/pages/extension/customer_list/chat?productId=".concat(this.ids))},setTouchMove:function(t){var e=this;t.touches[0].clientY<480&&t.touches[0].clientY>66&&(e.top=t.touches[0].clientY)}},created:function(){}});e.default=c},efd0:function(t,e,n){"use strict";var u=n("0c73"),c=n.n(u);c.a}}]);
;(global["webpackJsonp"] = global["webpackJsonp"] || []).push([
    'components/kefuIcon/index-create-component',
    {
        'components/kefuIcon/index-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('543d')['createComponent'](__webpack_require__("107b"))
        })
    },
    [['components/kefuIcon/index-create-component']]
]);
