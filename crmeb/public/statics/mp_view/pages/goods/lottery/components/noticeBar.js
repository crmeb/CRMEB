require('../../common/vendor.js');(global["webpackJsonp"]=global["webpackJsonp"]||[]).push([["pages/goods/lottery/components/noticeBar"],{3201:function(t,n,a){},4162:function(t,n,a){"use strict";var e=a("3201"),i=a.n(e);i.a},"4bd4":function(t,n,a){"use strict";a.r(n);var e=a("6ab9"),i=a("586e");for(var r in i)"default"!==r&&function(t){a.d(n,t,(function(){return i[t]}))}(r);a("4162");var o,s=a("f0c5"),u=Object(s["a"])(i["default"],e["b"],e["c"],!1,null,"7047ae9c",null,!1,e["a"],o);n["default"]=u.exports},"586e":function(t,n,a){"use strict";a.r(n);var e=a("fba3"),i=a.n(e);for(var r in e)"default"!==r&&function(t){a.d(n,t,(function(){return e[t]}))}(r);n["default"]=i.a},"6ab9":function(t,n,a){"use strict";var e;a.d(n,"b",(function(){return i})),a.d(n,"c",(function(){return r})),a.d(n,"a",(function(){return e}));var i=function(){var t=this,n=t.$createElement,a=(t._self._c,t.$t("恭喜您")),e=t.$t("获得");t.$mp.data=Object.assign({},{$root:{m0:a,m1:e}})},r=[]},fba3:function(t,n,a){"use strict";Object.defineProperty(n,"__esModule",{value:!0}),n.default=void 0;var e={name:"noticeBar",data:function(){return{animateUp:!1,listData:JSON.parse(JSON.stringify(this.showMsg)),timer:null}},props:{showMsg:{type:Array}},mounted:function(){this.timer=setInterval(this.scrollAnimate,2500)},methods:{scrollAnimate:function(){var t=this;this.animateUp=!0,setTimeout((function(){t.listData.push(t.listData[0]),t.listData.shift(),t.animateUp=!1}),500)}},destroyed:function(){clearInterval(this.timer)}};n.default=e}}]);
;(global["webpackJsonp"] = global["webpackJsonp"] || []).push([
    'pages/goods/lottery/components/noticeBar-create-component',
    {
        'pages/goods/lottery/components/noticeBar-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('543d')['createComponent'](__webpack_require__("4bd4"))
        })
    },
    [['pages/goods/lottery/components/noticeBar-create-component']]
]);
