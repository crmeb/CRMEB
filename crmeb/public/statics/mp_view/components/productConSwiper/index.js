(global["webpackJsonp"]=global["webpackJsonp"]||[]).push([["components/productConSwiper/index"],{"097d":function(t,n,e){"use strict";(function(t){Object.defineProperty(n,"__esModule",{value:!0}),n.default=void 0;var e={props:{imgUrls:{type:Array,default:function(){return[]}},videoline:{type:String,value:""}},data:function(){return{indicatorDots:!0,circular:!0,autoplay:!0,interval:3e3,duration:500,currents:"1",controls:!0,isPlay:!0,videoContext:""}},mounted:function(){this.videoline&&this.imgUrls.shift(),this.videoContext=t.createVideoContext("myVideo",this)},methods:{videoPause:function(t){},bindPause:function(){this.videoContext.play(),this.$set(this,"controls",!1),this.autoplay=!1},change:function(t){this.$set(this,"currents",t.detail.current+1)}}};n.default=e}).call(this,e("543d")["default"])},6319:function(t,n,e){"use strict";var i=e("c0b1"),o=e.n(i);o.a},"86c2":function(t,n,e){"use strict";e.r(n);var i=e("a5d6"),o=e("9b4a");for(var r in o)"default"!==r&&function(t){e.d(n,t,(function(){return o[t]}))}(r);e("6319");var u,a=e("f0c5"),c=Object(a["a"])(o["default"],i["b"],i["c"],!1,null,"60864078",null,!1,i["a"],u);n["default"]=c.exports},"9b4a":function(t,n,e){"use strict";e.r(n);var i=e("097d"),o=e.n(i);for(var r in i)"default"!==r&&function(t){e.d(n,t,(function(){return i[t]}))}(r);n["default"]=o.a},a5d6:function(t,n,e){"use strict";var i;e.d(n,"b",(function(){return o})),e.d(n,"c",(function(){return r})),e.d(n,"a",(function(){return i}));var o=function(){var t=this,n=t.$createElement;t._self._c},r=[]},c0b1:function(t,n,e){}}]);
;(global["webpackJsonp"] = global["webpackJsonp"] || []).push([
    'components/productConSwiper/index-create-component',
    {
        'components/productConSwiper/index-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('543d')['createComponent'](__webpack_require__("86c2"))
        })
    },
    [['components/productConSwiper/index-create-component']]
]);
