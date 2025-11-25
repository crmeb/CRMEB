require('../../common/vendor.js');(global["webpackJsonp"]=global["webpackJsonp"]||[]).push([["pages/goods/lottery/components/noticeBar"],{"359c":function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var i={name:"noticeBar",data:function(){return{animateUp:!1,listData:JSON.parse(JSON.stringify(this.showMsg)),timer:null}},props:{showMsg:{type:Array}},mounted:function(){this.timer=setInterval(this.scrollAnimate,2500)},methods:{scrollAnimate:function(){var t=this;this.animateUp=!0,setTimeout((function(){t.listData.push(t.listData[0]),t.listData.shift(),t.animateUp=!1}),500)}},destroyed:function(){clearInterval(this.timer)}};e.default=i},e9fe:function(t,e,n){"use strict";n.d(e,"b",(function(){return i})),n.d(e,"c",(function(){return a})),n.d(e,"a",(function(){}));var i=function(){var t=this.$createElement,e=(this._self._c,this.$t("恭喜您")),n=this.$t("获得");this.$mp.data=Object.assign({},{$root:{m0:e,m1:n}})},a=[]},ed29:function(t,e,n){"use strict";var i=n("f0e0"),a=n.n(i);a.a},eefe:function(t,e,n){"use strict";n.r(e);var i=n("359c"),a=n.n(i);for(var r in i)["default"].indexOf(r)<0&&function(t){n.d(e,t,(function(){return i[t]}))}(r);e["default"]=a.a},f0e0:function(t,e,n){},f715:function(t,e,n){"use strict";n.r(e);var i=n("e9fe"),a=n("eefe");for(var r in a)["default"].indexOf(r)<0&&function(t){n.d(e,t,(function(){return a[t]}))}(r);n("ed29");var s=n("828b"),o=Object(s["a"])(a["default"],i["b"],i["c"],!1,null,"303a5a66",null,!1,i["a"],void 0);e["default"]=o.exports}}]);
;(global["webpackJsonp"] = global["webpackJsonp"] || []).push([
    'pages/goods/lottery/components/noticeBar-create-component',
    {
        'pages/goods/lottery/components/noticeBar-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('df3c')['createComponent'](__webpack_require__("f715"))
        })
    },
    [['pages/goods/lottery/components/noticeBar-create-component']]
]);
