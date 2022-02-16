(global["webpackJsonp"]=global["webpackJsonp"]||[]).push([["pages/order_pay_status/components/lotteryAleart"],{"27ad":function(t,a,e){"use strict";Object.defineProperty(a,"__esModule",{value:!0}),a.default=void 0;var n={data:function(){return{aleartData:{}}},props:{aleartType:{type:Number},alData:{type:Object},aleartStatus:{type:Boolean,default:!1}},watch:{aleartType:function(t){1===t?this.aleartData={title:"暂无抽奖资格",msg:"1、您未关注公众号\n2、您未获得VIP权限，获取VIP途径：\n（1）购买过打通版的用户可在会员群联系官方客服开通\n（2）官方小程序商城购买CRMEB打通版、企业版后自动开通",btn:"我知道了"}:2===t&&(this.aleartData={title:"抽奖结果",img:this.alData.image,msg:this.alData.prompt,btn:"好的",type:this.alData.type})},aleartStatus:function(t){t||(this.aleartData={})}},methods:{posterImageClose:function(t){this.$emit("close",!1)}}};a.default=n},"4d9b":function(t,a,e){"use strict";var n;e.d(a,"b",(function(){return r})),e.d(a,"c",(function(){return u})),e.d(a,"a",(function(){return n}));var r=function(){var t=this,a=t.$createElement;t._self._c},u=[]},"69c1":function(t,a,e){"use strict";e.r(a);var n=e("4d9b"),r=e("7e9e");for(var u in r)"default"!==u&&function(t){e.d(a,t,(function(){return r[t]}))}(u);e("f9e6e");var i,o=e("f0c5"),l=Object(o["a"])(r["default"],n["b"],n["c"],!1,null,"9a39f772",null,!1,n["a"],i);a["default"]=l.exports},"7e9e":function(t,a,e){"use strict";e.r(a);var n=e("27ad"),r=e.n(n);for(var u in n)"default"!==u&&function(t){e.d(a,t,(function(){return n[t]}))}(u);a["default"]=r.a},b4ef:function(t,a,e){},f9e6e:function(t,a,e){"use strict";var n=e("b4ef"),r=e.n(n);r.a}}]);
;(global["webpackJsonp"] = global["webpackJsonp"] || []).push([
    'pages/order_pay_status/components/lotteryAleart-create-component',
    {
        'pages/order_pay_status/components/lotteryAleart-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('543d')['createComponent'](__webpack_require__("69c1"))
        })
    },
    [['pages/order_pay_status/components/lotteryAleart-create-component']]
]);
