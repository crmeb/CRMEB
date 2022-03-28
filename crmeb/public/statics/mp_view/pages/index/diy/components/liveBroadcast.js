(global["webpackJsonp"]=global["webpackJsonp"]||[]).push([["pages/index/diy/components/liveBroadcast"],{"0130":function(t,n,i){},"0621":function(t,n,i){"use strict";i.r(n);var e=i("e35a"),a=i("6c16");for(var o in a)"default"!==o&&function(t){i.d(n,t,(function(){return a[t]}))}(o);i("53f6");var u,c=i("f0c5"),s=Object(c["a"])(a["default"],e["b"],e["c"],!1,null,null,null,!1,e["a"],u);n["default"]=s.exports},"53f6":function(t,n,i){"use strict";var e=i("0130"),a=i.n(e);a.a},"6c16":function(t,n,i){"use strict";i.r(n);var e=i("784a"),a=i.n(e);for(var o in e)"default"!==o&&function(t){i.d(n,t,(function(){return e[t]}))}(o);n["default"]=a.a},"784a":function(t,n,i){"use strict";Object.defineProperty(n,"__esModule",{value:!0}),n.default=void 0;var e=i("f44a"),a={name:"liveBroadcast",props:{dataConfig:{type:Object,default:function(){}},isSortType:{type:String|Number,default:0}},data:function(){return{listStyle:this.dataConfig.listStyle.type,mbConfig:this.dataConfig.mbConfig.val,liveList:[],custom_params:""}},created:function(){},mounted:function(){this.custom_params=encodeURIComponent(JSON.stringify({spid:this.$store.state.app.uid})),this.getLiveList()},methods:{getLiveList:function(){var t=this;this.$config.LIMIT;(0,e.getLiveList)(1,void 0==this.limit?10:this.limit).then((function(n){t.liveList=n.data})).catch((function(t){}))}}};n.default=a},e35a:function(t,n,i){"use strict";var e;i.d(n,"b",(function(){return a})),i.d(n,"c",(function(){return o})),i.d(n,"a",(function(){return e}));var a=function(){var t=this,n=t.$createElement;t._self._c},o=[]}}]);
;(global["webpackJsonp"] = global["webpackJsonp"] || []).push([
    'pages/index/diy/components/liveBroadcast-create-component',
    {
        'pages/index/diy/components/liveBroadcast-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('543d')['createComponent'](__webpack_require__("0621"))
        })
    },
    [['pages/index/diy/components/liveBroadcast-create-component']]
]);
