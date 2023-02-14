require('../../common/vendor.js');(global["webpackJsonp"]=global["webpackJsonp"]||[]).push([["pages/goods/lottery/components/noticeBar"],{"0f3b":function(t,e,n){"use strict";n.r(e);var a=n("5d9e2"),i=n("5caa");for(var r in i)"default"!==r&&function(t){n.d(e,t,(function(){return i[t]}))}(r);n("ee54");var o,s=n("f0c5"),u=Object(s["a"])(i["default"],a["b"],a["c"],!1,null,"7047ae9c",null,!1,a["a"],o);e["default"]=u.exports},"4e47":function(t,e,n){},"5caa":function(t,e,n){"use strict";n.r(e);var a=n("79a0"),i=n.n(a);for(var r in a)"default"!==r&&function(t){n.d(e,t,(function(){return a[t]}))}(r);e["default"]=i.a},"5d9e2":function(t,e,n){"use strict";var a;n.d(e,"b",(function(){return i})),n.d(e,"c",(function(){return r})),n.d(e,"a",(function(){return a}));var i=function(){var t=this,e=t.$createElement,n=(t._self._c,t.$t("恭喜您")),a=t.$t("获得");t.$mp.data=Object.assign({},{$root:{m0:n,m1:a}})},r=[]},"79a0":function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var a={name:"noticeBar",data:function(){return{animateUp:!1,listData:JSON.parse(JSON.stringify(this.showMsg)),timer:null}},props:{showMsg:{type:Array}},mounted:function(){this.timer=setInterval(this.scrollAnimate,2500)},methods:{scrollAnimate:function(){var t=this;this.animateUp=!0,setTimeout((function(){t.listData.push(t.listData[0]),t.listData.shift(),t.animateUp=!1}),500)}},destroyed:function(){clearInterval(this.timer)}};e.default=a},ee54:function(t,e,n){"use strict";var a=n("4e47"),i=n.n(a);i.a}}]);
;(global["webpackJsonp"] = global["webpackJsonp"] || []).push([
    'pages/goods/lottery/components/noticeBar-create-component',
    {
        'pages/goods/lottery/components/noticeBar-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('543d')['createComponent'](__webpack_require__("0f3b"))
        })
    },
    [['pages/goods/lottery/components/noticeBar-create-component']]
]);
