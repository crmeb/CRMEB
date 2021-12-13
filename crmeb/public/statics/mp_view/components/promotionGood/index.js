(global["webpackJsonp"]=global["webpackJsonp"]||[]).push([["components/promotionGood/index"],{"0f8b":function(t,n,e){"use strict";e.r(n);var u=e("3a28"),a=e("8f4b");for(var o in a)"default"!==o&&function(t){e.d(n,t,(function(){return a[t]}))}(o);e("a131");var i,r=e("f0c5"),c=Object(r["a"])(a["default"],u["b"],u["c"],!1,null,"66a2b9b9",null,!1,u["a"],i);n["default"]=c.exports},"1a6c":function(t,n,e){"use strict";(function(t){Object.defineProperty(n,"__esModule",{value:!0}),n.default=void 0;var u=e("26cb"),a=e("4729"),o=i(e("c83f"));function i(t){return t&&t.__esModule?t:{default:t}}var r={computed:(0,u.mapGetters)(["uid"]),mixins:[o.default],props:{benefit:{type:Array,default:function(){return[]}}},data:function(){return{}},methods:{goDetail:function(n){var e=this;(0,a.goPage)().then((function(u){(0,a.goShopDetail)(n,e.uid).then((function(e){t.navigateTo({url:"/pages/goods_details/index?id=".concat(n.id)})}))}))}}};n.default=r}).call(this,e("543d")["default"])},"3a28":function(t,n,e){"use strict";var u;e.d(n,"b",(function(){return a})),e.d(n,"c",(function(){return o})),e.d(n,"a",(function(){return u}));var a=function(){var t=this,n=t.$createElement;t._self._c},o=[]},"5e6f":function(t,n,e){},"8f4b":function(t,n,e){"use strict";e.r(n);var u=e("1a6c"),a=e.n(u);for(var o in u)"default"!==o&&function(t){e.d(n,t,(function(){return u[t]}))}(o);n["default"]=a.a},a131:function(t,n,e){"use strict";var u=e("5e6f"),a=e.n(u);a.a}}]);
;(global["webpackJsonp"] = global["webpackJsonp"] || []).push([
    'components/promotionGood/index-create-component',
    {
        'components/promotionGood/index-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('543d')['createComponent'](__webpack_require__("0f8b"))
        })
    },
    [['components/promotionGood/index-create-component']]
]);
