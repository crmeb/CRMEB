(global["webpackJsonp"]=global["webpackJsonp"]||[]).push([["components/recommend/index"],{"22c8":function(t,n,e){"use strict";(function(t){Object.defineProperty(n,"__esModule",{value:!0}),n.default=void 0;var u=e("26cb"),a=e("4729"),c=r(e("c83f"));function r(t){return t&&t.__esModule?t:{default:t}}var o={computed:(0,u.mapGetters)(["uid"]),props:{hostProduct:{type:Array,default:function(){return[]}}},mixins:[c.default],data:function(){return{}},methods:{goDetail:function(n){(0,a.goShopDetail)(n,this.uid).then((function(e){t.navigateTo({url:"/pages/goods_details/index?id=".concat(n.id)})}))}}};n.default=o}).call(this,e("543d")["default"])},"22f5":function(t,n,e){"use strict";e.r(n);var u=e("a1d8"),a=e("a4f0");for(var c in a)"default"!==c&&function(t){e.d(n,t,(function(){return a[t]}))}(c);e("6df4");var r,o=e("f0c5"),i=Object(o["a"])(a["default"],u["b"],u["c"],!1,null,"628ebb5a",null,!1,u["a"],r);n["default"]=i.exports},"6df4":function(t,n,e){"use strict";var u=e("ac943"),a=e.n(u);a.a},a1d8:function(t,n,e){"use strict";var u;e.d(n,"b",(function(){return a})),e.d(n,"c",(function(){return c})),e.d(n,"a",(function(){return u}));var a=function(){var t=this,n=t.$createElement;t._self._c},c=[]},a4f0:function(t,n,e){"use strict";e.r(n);var u=e("22c8"),a=e.n(u);for(var c in u)"default"!==c&&function(t){e.d(n,t,(function(){return u[t]}))}(c);n["default"]=a.a},ac943:function(t,n,e){}}]);
;(global["webpackJsonp"] = global["webpackJsonp"] || []).push([
    'components/recommend/index-create-component',
    {
        'components/recommend/index-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('543d')['createComponent'](__webpack_require__("22f5"))
        })
    },
    [['components/recommend/index-create-component']]
]);
