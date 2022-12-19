(global["webpackJsonp"]=global["webpackJsonp"]||[]).push([["components/promotionGood/index"],{"0c2c":function(t,n,e){"use strict";(function(t){Object.defineProperty(n,"__esModule",{value:!0}),n.default=void 0;var u=e("26cb"),a=e("a43a"),o=c(e("9ad2"));function c(t){return t&&t.__esModule?t:{default:t}}var i={computed:(0,u.mapGetters)(["uid"]),mixins:[o.default],props:{benefit:{type:Array,default:function(){return[]}}},data:function(){return{}},methods:{goDetail:function(n){var e=this;(0,a.goPage)().then((function(u){(0,a.goShopDetail)(n,e.uid).then((function(e){t.navigateTo({url:"/pages/goods_details/index?id=".concat(n.id)})}))}))}}};n.default=i}).call(this,e("543d")["default"])},1129:function(t,n,e){"use strict";e.r(n);var u=e("0c2c"),a=e.n(u);for(var o in u)"default"!==o&&function(t){e.d(n,t,(function(){return u[t]}))}(o);n["default"]=a.a},"35df":function(t,n,e){"use strict";var u=e("4bc9"),a=e.n(u);a.a},"4bc9":function(t,n,e){},"75dc":function(t,n,e){"use strict";e.r(n);var u=e("f393"),a=e("1129");for(var o in a)"default"!==o&&function(t){e.d(n,t,(function(){return a[t]}))}(o);e("35df");var c,i=e("f0c5"),r=Object(i["a"])(a["default"],u["b"],u["c"],!1,null,"4dae15d0",null,!1,u["a"],c);n["default"]=r.exports},f393:function(t,n,e){"use strict";var u;e.d(n,"b",(function(){return a})),e.d(n,"c",(function(){return o})),e.d(n,"a",(function(){return u}));var a=function(){var t=this,n=t.$createElement,e=(t._self._c,t.$t("￥"));t.$mp.data=Object.assign({},{$root:{m0:e}})},o=[]}}]);
;(global["webpackJsonp"] = global["webpackJsonp"] || []).push([
    'components/promotionGood/index-create-component',
    {
        'components/promotionGood/index-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('543d')['createComponent'](__webpack_require__("75dc"))
        })
    },
    [['components/promotionGood/index-create-component']]
]);
