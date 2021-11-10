(global["webpackJsonp"]=global["webpackJsonp"]||[]).push([["pages/index/components/customerService"],{"0097":function(t,n,e){"use strict";var o;e.d(n,"b",(function(){return i})),e.d(n,"c",(function(){return a})),e.d(n,"a",(function(){return o}));var i=function(){var t=this,n=t.$createElement;t._self._c},a=[]},"1fb4":function(t,n,e){"use strict";e.r(n);var o=e("4f65"),i=e.n(o);for(var a in o)"default"!==a&&function(t){e.d(n,t,(function(){return o[t]}))}(a);n["default"]=i.a},"4f65":function(t,n,e){"use strict";Object.defineProperty(n,"__esModule",{value:!0}),n.default=void 0;var o=getApp(),i={name:"customerService",props:{dataConfig:{type:Object,default:function(){}}},watch:{dataConfig:{immediate:!0,handler:function(t,n){t&&(this.logoConfig=t.imgUrl.url,this.isShow=t.isShow.val,this.routineContact=t.routine_contact_type)}}},data:function(){return{logoConfig:"",topConfig:"200px",name:this.$options.name,isIframe:!1,isShow:!0,routineContact:"0"}},created:function(){this.isIframe=o.globalData.isIframe},methods:{setTouchMove:function(t){var n=this;t.touches[0].clientY<545&&t.touches[0].clientY>66&&(n.topConfig=t.touches[0].clientY+"px")}}};n.default=i},"6f1b":function(t,n,e){"use strict";var o=e("f0e4"),i=e.n(o);i.a},ede5:function(t,n,e){"use strict";e.r(n);var o=e("0097"),i=e("1fb4");for(var a in i)"default"!==a&&function(t){e.d(n,t,(function(){return i[t]}))}(a);e("6f1b");var u,c=e("f0c5"),r=Object(c["a"])(i["default"],o["b"],o["c"],!1,null,null,null,!1,o["a"],u);n["default"]=r.exports},f0e4:function(t,n,e){}}]);
;(global["webpackJsonp"] = global["webpackJsonp"] || []).push([
    'pages/index/components/customerService-create-component',
    {
        'pages/index/components/customerService-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('543d')['createComponent'](__webpack_require__("ede5"))
        })
    },
    [['pages/index/components/customerService-create-component']]
]);
