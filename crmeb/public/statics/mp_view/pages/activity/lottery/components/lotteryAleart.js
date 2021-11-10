(global["webpackJsonp"]=global["webpackJsonp"]||[]).push([["pages/activity/lottery/components/lotteryAleart"],{"77eb":function(t,a,e){"use strict";Object.defineProperty(a,"__esModule",{value:!0}),a.default=void 0;var n={data:function(){return{aleartData:{}}},props:{aleartType:{type:Number},alData:{type:Object},aleartStatus:{type:Boolean,default:!1}},watch:{aleartType:function(t){1===t?this.aleartData={title:"暂无抽奖资格",msg:"1、您未关注公众号\n2、您未获得VIP权限，获取VIP途径：\n（1）购买过打通版的用户可在会员群联系官方客服开通\n（2）官方小程序商城购买CRMEB打通版、企业版后自动开通",btn:"我知道了"}:2===t&&(this.aleartData={title:"抽奖结果",img:this.alData.image,msg:this.alData.prompt,btn:"好的",type:this.alData.type})},aleartStatus:function(t){t||(this.aleartData={})}},methods:{posterImageClose:function(t){this.$emit("close",!1)}}};a.default=n},ac90:function(t,a,e){"use strict";e.r(a);var n=e("77eb"),r=e.n(n);for(var c in n)"default"!==c&&function(t){e.d(a,t,(function(){return n[t]}))}(c);a["default"]=r.a},c670:function(t,a,e){"use strict";var n;e.d(a,"b",(function(){return r})),e.d(a,"c",(function(){return c})),e.d(a,"a",(function(){return n}));var r=function(){var t=this,a=t.$createElement;t._self._c},c=[]},ccac:function(t,a,e){},f7b4:function(t,a,e){"use strict";e.r(a);var n=e("c670"),r=e("ac90");for(var c in r)"default"!==c&&function(t){e.d(a,t,(function(){return r[t]}))}(c);e("fe2e");var u,i=e("f0c5"),l=Object(i["a"])(r["default"],n["b"],n["c"],!1,null,"264cea95",null,!1,n["a"],u);a["default"]=l.exports},fe2e:function(t,a,e){"use strict";var n=e("ccac"),r=e.n(n);r.a}}]);
;(global["webpackJsonp"] = global["webpackJsonp"] || []).push([
    'pages/activity/lottery/components/lotteryAleart-create-component',
    {
        'pages/activity/lottery/components/lotteryAleart-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('543d')['createComponent'](__webpack_require__("f7b4"))
        })
    },
    [['pages/activity/lottery/components/lotteryAleart-create-component']]
]);
