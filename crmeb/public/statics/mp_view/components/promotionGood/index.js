(global["webpackJsonp"]=global["webpackJsonp"]||[]).push([["components/promotionGood/index"],{"0f8b":function(t,n,e){"use strict";e.r(n);var u=e("e93c"),o=e("8f4b");for(var a in o)"default"!==a&&function(t){e.d(n,t,(function(){return o[t]}))}(a);e("756b");var i,r=e("f0c5"),c=Object(r["a"])(o["default"],u["b"],u["c"],!1,null,"2bd95962",null,!1,u["a"],i);n["default"]=c.exports},"1a6c":function(t,n,e){"use strict";(function(t){Object.defineProperty(n,"__esModule",{value:!0}),n.default=void 0;var u=e("26cb"),o=e("4729"),a=i(e("c83f"));function i(t){return t&&t.__esModule?t:{default:t}}var r={computed:(0,u.mapGetters)(["uid"]),mixins:[a.default],props:{benefit:{type:Array,default:function(){return[]}}},data:function(){return{}},methods:{goDetail:function(n){var e=this;(0,o.goPage)().then((function(u){(0,o.goShopDetail)(n,e.uid).then((function(e){t.navigateTo({url:"/pages/goods_details/index?id=".concat(n.id)})}))}))}}};n.default=r}).call(this,e("543d")["default"])},"756b":function(t,n,e){"use strict";var u=e("f45e"),o=e.n(u);o.a},"8f4b":function(t,n,e){"use strict";e.r(n);var u=e("1a6c"),o=e.n(u);for(var a in u)"default"!==a&&function(t){e.d(n,t,(function(){return u[t]}))}(a);n["default"]=o.a},e93c:function(t,n,e){"use strict";var u;e.d(n,"b",(function(){return o})),e.d(n,"c",(function(){return a})),e.d(n,"a",(function(){return u}));var o=function(){var t=this,n=t.$createElement;t._self._c},a=[]},f45e:function(t,n,e){}}]);
;(global["webpackJsonp"] = global["webpackJsonp"] || []).push([
    'components/promotionGood/index-create-component',
    {
        'components/promotionGood/index-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('543d')['createComponent'](__webpack_require__("0f8b"))
        })
    },
    [['components/promotionGood/index-create-component']]
]);
