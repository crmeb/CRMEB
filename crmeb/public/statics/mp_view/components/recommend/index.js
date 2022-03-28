(global["webpackJsonp"]=global["webpackJsonp"]||[]).push([["components/recommend/index"],{"3f4c":function(t,n,e){},"94e7":function(t,n,e){"use strict";var u=e("3f4c"),a=e.n(u);a.a},"9e1c":function(t,n,e){"use strict";var u;e.d(n,"b",(function(){return a})),e.d(n,"c",(function(){return c})),e.d(n,"a",(function(){return u}));var a=function(){var t=this,n=t.$createElement;t._self._c},c=[]},ad62:function(t,n,e){"use strict";(function(t){Object.defineProperty(n,"__esModule",{value:!0}),n.default=void 0;var u=e("26cb"),a=e("1754"),c=r(e("2d3a"));function r(t){return t&&t.__esModule?t:{default:t}}var o={computed:(0,u.mapGetters)(["uid"]),props:{hostProduct:{type:Array,default:function(){return[]}}},mixins:[c.default],data:function(){return{}},methods:{goDetail:function(n){(0,a.goShopDetail)(n,this.uid).then((function(e){t.navigateTo({url:"/pages/goods_details/index?id=".concat(n.id)})}))}}};n.default=o}).call(this,e("543d")["default"])},c939:function(t,n,e){"use strict";e.r(n);var u=e("ad62"),a=e.n(u);for(var c in u)"default"!==c&&function(t){e.d(n,t,(function(){return u[t]}))}(c);n["default"]=a.a},ecac:function(t,n,e){"use strict";e.r(n);var u=e("9e1c"),a=e("c939");for(var c in a)"default"!==c&&function(t){e.d(n,t,(function(){return a[t]}))}(c);e("94e7");var r,o=e("f0c5"),i=Object(o["a"])(a["default"],u["b"],u["c"],!1,null,"628ebb5a",null,!1,u["a"],r);n["default"]=i.exports}}]);
;(global["webpackJsonp"] = global["webpackJsonp"] || []).push([
    'components/recommend/index-create-component',
    {
        'components/recommend/index-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('543d')['createComponent'](__webpack_require__("ecac"))
        })
    },
    [['components/recommend/index-create-component']]
]);
