(global["webpackJsonp"]=global["webpackJsonp"]||[]).push([["components/recommend/index"],{"22c8":function(t,n,e){"use strict";(function(t){Object.defineProperty(n,"__esModule",{value:!0}),n.default=void 0;var u=e("26cb"),a=e("4729"),r=o(e("c83f"));function o(t){return t&&t.__esModule?t:{default:t}}var c={computed:(0,u.mapGetters)(["uid"]),props:{hostProduct:{type:Array,default:function(){return[]}}},mixins:[r.default],data:function(){return{}},methods:{goDetail:function(n){(0,a.goShopDetail)(n,this.uid).then((function(e){t.navigateTo({url:"/pages/goods_details/index?id=".concat(n.id)})}))}}};n.default=c}).call(this,e("543d")["default"])},"22f5":function(t,n,e){"use strict";e.r(n);var u=e("a1d8"),a=e("a4f0");for(var r in a)"default"!==r&&function(t){e.d(n,t,(function(){return a[t]}))}(r);e("6df4");var o,c=e("f0c5"),i=Object(c["a"])(a["default"],u["b"],u["c"],!1,null,"628ebb5a",null,!1,u["a"],o);n["default"]=i.exports},6918:function(t,n,e){},"6df4":function(t,n,e){"use strict";var u=e("6918"),a=e.n(u);a.a},a1d8:function(t,n,e){"use strict";var u;e.d(n,"b",(function(){return a})),e.d(n,"c",(function(){return r})),e.d(n,"a",(function(){return u}));var a=function(){var t=this,n=t.$createElement;t._self._c},r=[]},a4f0:function(t,n,e){"use strict";e.r(n);var u=e("22c8"),a=e.n(u);for(var r in u)"default"!==r&&function(t){e.d(n,t,(function(){return u[t]}))}(r);n["default"]=a.a}}]);
;(global["webpackJsonp"] = global["webpackJsonp"] || []).push([
    'components/recommend/index-create-component',
    {
        'components/recommend/index-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('543d')['createComponent'](__webpack_require__("22f5"))
        })
    },
    [['components/recommend/index-create-component']]
]);
