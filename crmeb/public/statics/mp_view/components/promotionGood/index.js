(global["webpackJsonp"]=global["webpackJsonp"]||[]).push([["components/promotionGood/index"],{"0f8b":function(t,n,e){"use strict";e.r(n);var u=e("308c"),o=e("8f4b");for(var a in o)"default"!==a&&function(t){e.d(n,t,(function(){return o[t]}))}(a);e("146d");var c,i=e("f0c5"),r=Object(i["a"])(o["default"],u["b"],u["c"],!1,null,"0507b3cf",null,!1,u["a"],c);n["default"]=r.exports},"146d":function(t,n,e){"use strict";var u=e("fe39"),o=e.n(u);o.a},"1a6c":function(t,n,e){"use strict";(function(t){Object.defineProperty(n,"__esModule",{value:!0}),n.default=void 0;var u=e("26cb"),o=e("4729"),a=c(e("c83f"));function c(t){return t&&t.__esModule?t:{default:t}}var i={computed:(0,u.mapGetters)(["uid"]),mixins:[a.default],props:{benefit:{type:Array,default:function(){return[]}}},data:function(){return{}},methods:{goDetail:function(n){var e=this;(0,o.goPage)().then((function(u){(0,o.goShopDetail)(n,e.uid).then((function(e){t.navigateTo({url:"/pages/goods_details/index?id=".concat(n.id)})}))}))}}};n.default=i}).call(this,e("543d")["default"])},"308c":function(t,n,e){"use strict";var u;e.d(n,"b",(function(){return o})),e.d(n,"c",(function(){return a})),e.d(n,"a",(function(){return u}));var o=function(){var t=this,n=t.$createElement;t._self._c},a=[]},"8f4b":function(t,n,e){"use strict";e.r(n);var u=e("1a6c"),o=e.n(u);for(var a in u)"default"!==a&&function(t){e.d(n,t,(function(){return u[t]}))}(a);n["default"]=o.a},fe39:function(t,n,e){}}]);
;(global["webpackJsonp"] = global["webpackJsonp"] || []).push([
    'components/promotionGood/index-create-component',
    {
        'components/promotionGood/index-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('543d')['createComponent'](__webpack_require__("0f8b"))
        })
    },
    [['components/promotionGood/index-create-component']]
]);
