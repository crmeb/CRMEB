(global["webpackJsonp"]=global["webpackJsonp"]||[]).push([["components/kefuIcon/index"],{"138b":function(t,e,n){"use strict";n.r(e);var u=n("d59a"),o=n("b129");for(var c in o)["default"].indexOf(c)<0&&function(t){n.d(e,t,(function(){return o[t]}))}(c);n("3e39");var f=n("f0c5"),i=Object(f["a"])(o["default"],u["b"],u["c"],!1,null,null,null,!1,u["a"],void 0);e["default"]=i.exports},"3e39":function(t,e,n){"use strict";var u=n("785f"),o=n.n(u);o.a},"785f":function(t,e,n){},"7e9f":function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var u=n("f9f0"),o=(getApp(),{name:"kefuIcon",props:{ids:{type:Number,default:0},routineContact:{type:Number,default:0},storeInfo:{type:Object,default:function(){}},goodsCon:{type:Number,default:0}},data:function(){return{top:"480"}},mounted:function(){},methods:{goCustomer:function(){(0,u.getCustomer)("/pages/extension/customer_list/chat?productId=".concat(this.ids))},setTouchMove:function(t){t.touches[0].clientY<480&&t.touches[0].clientY>66&&(this.top=t.touches[0].clientY)}},created:function(){}});e.default=o},b129:function(t,e,n){"use strict";n.r(e);var u=n("7e9f"),o=n.n(u);for(var c in u)["default"].indexOf(c)<0&&function(t){n.d(e,t,(function(){return u[t]}))}(c);e["default"]=o.a},d59a:function(t,e,n){"use strict";n.d(e,"b",(function(){return u})),n.d(e,"c",(function(){return o})),n.d(e,"a",(function(){}));var u=function(){var t=this.$createElement;this._self._c},o=[]}}]);
;(global["webpackJsonp"] = global["webpackJsonp"] || []).push([
    'components/kefuIcon/index-create-component',
    {
        'components/kefuIcon/index-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('543d')['createComponent'](__webpack_require__("138b"))
        })
    },
    [['components/kefuIcon/index-create-component']]
]);
