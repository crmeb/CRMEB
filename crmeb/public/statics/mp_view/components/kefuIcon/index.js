(global["webpackJsonp"]=global["webpackJsonp"]||[]).push([["components/kefuIcon/index"],{"270a0":function(t,e,n){"use strict";n.d(e,"b",(function(){return u})),n.d(e,"c",(function(){return c})),n.d(e,"a",(function(){}));var u=function(){var t=this.$createElement;this._self._c},c=[]},"2ce97":function(t,e,n){"use strict";n.r(e);var u=n("5fae"),c=n.n(u);for(var o in u)["default"].indexOf(o)<0&&function(t){n.d(e,t,(function(){return u[t]}))}(o);e["default"]=c.a},4063:function(t,e,n){"use strict";n.r(e);var u=n("270a0"),c=n("2ce97");for(var o in c)["default"].indexOf(o)<0&&function(t){n.d(e,t,(function(){return c[t]}))}(o);n("fcdc");var i=n("828b"),f=Object(i["a"])(c["default"],u["b"],u["c"],!1,null,null,null,!1,u["a"],void 0);e["default"]=f.exports},"5fae":function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var u=n("c8ec"),c=(getApp(),{name:"kefuIcon",props:{ids:{type:Number,default:0},routineContact:{type:Number,default:0},storeInfo:{type:Object,default:function(){}},goodsCon:{type:Number,default:0}},data:function(){return{top:"480"}},mounted:function(){},methods:{goCustomer:function(){(0,u.getCustomer)("/pages/extension/customer_list/chat?productId=".concat(this.ids))},setTouchMove:function(t){t.touches[0].clientY<480&&t.touches[0].clientY>66&&(this.top=t.touches[0].clientY)}},created:function(){}});e.default=c},"99e6":function(t,e,n){},fcdc:function(t,e,n){"use strict";var u=n("99e6"),c=n.n(u);c.a}}]);
;(global["webpackJsonp"] = global["webpackJsonp"] || []).push([
    'components/kefuIcon/index-create-component',
    {
        'components/kefuIcon/index-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('df3c')['createComponent'](__webpack_require__("4063"))
        })
    },
    [['components/kefuIcon/index-create-component']]
]);
