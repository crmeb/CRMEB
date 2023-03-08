require('../../common/vendor.js');(global["webpackJsonp"]=global["webpackJsonp"]||[]).push([["pages/goods/lottery/components/noticeBar"],{3998:function(t,n,e){"use strict";Object.defineProperty(n,"__esModule",{value:!0}),n.default=void 0;var a={name:"noticeBar",data:function(){return{animateUp:!1,listData:JSON.parse(JSON.stringify(this.showMsg)),timer:null}},props:{showMsg:{type:Array}},mounted:function(){this.timer=setInterval(this.scrollAnimate,2500)},methods:{scrollAnimate:function(){var t=this;this.animateUp=!0,setTimeout((function(){t.listData.push(t.listData[0]),t.listData.shift(),t.animateUp=!1}),500)}},destroyed:function(){clearInterval(this.timer)}};n.default=a},"8a81":function(t,n,e){"use strict";var a=e("9f19"),i=e.n(a);i.a},"8d71":function(t,n,e){"use strict";var a;e.d(n,"b",(function(){return i})),e.d(n,"c",(function(){return r})),e.d(n,"a",(function(){return a}));var i=function(){var t=this,n=t.$createElement,e=(t._self._c,t.$t("恭喜您")),a=t.$t("获得");t.$mp.data=Object.assign({},{$root:{m0:e,m1:a}})},r=[]},"9f19":function(t,n,e){},e05b:function(t,n,e){"use strict";e.r(n);var a=e("8d71"),i=e("e6d5");for(var r in i)"default"!==r&&function(t){e.d(n,t,(function(){return i[t]}))}(r);e("8a81");var o,s=e("f0c5"),u=Object(s["a"])(i["default"],a["b"],a["c"],!1,null,"7047ae9c",null,!1,a["a"],o);n["default"]=u.exports},e6d5:function(t,n,e){"use strict";e.r(n);var a=e("3998"),i=e.n(a);for(var r in a)"default"!==r&&function(t){e.d(n,t,(function(){return a[t]}))}(r);n["default"]=i.a}}]);
;(global["webpackJsonp"] = global["webpackJsonp"] || []).push([
    'pages/goods/lottery/components/noticeBar-create-component',
    {
        'pages/goods/lottery/components/noticeBar-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('543d')['createComponent'](__webpack_require__("e05b"))
        })
    },
    [['pages/goods/lottery/components/noticeBar-create-component']]
]);
