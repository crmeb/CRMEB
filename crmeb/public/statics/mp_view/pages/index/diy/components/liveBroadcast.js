(global["webpackJsonp"]=global["webpackJsonp"]||[]).push([["pages/index/diy/components/liveBroadcast"],{"2f99":function(t,n,i){"use strict";i.r(n);var e=i("45b58"),a=i.n(e);for(var o in e)"default"!==o&&function(t){i.d(n,t,(function(){return e[t]}))}(o);n["default"]=a.a},"45b58":function(t,n,i){"use strict";Object.defineProperty(n,"__esModule",{value:!0}),n.default=void 0;var e=i("c159"),a={name:"liveBroadcast",props:{dataConfig:{type:Object,default:function(){}},isSortType:{type:String|Number,default:0}},data:function(){return{listStyle:this.dataConfig.listStyle.type,mbConfig:this.dataConfig.mbConfig.val,liveList:[],custom_params:""}},created:function(){},mounted:function(){this.custom_params=encodeURIComponent(JSON.stringify({spid:this.$store.state.app.uid})),this.getLiveList()},methods:{getLiveList:function(){var t=this;this.$config.LIMIT;(0,e.getLiveList)(1,void 0==this.limit?10:this.limit).then((function(n){t.liveList=n.data})).catch((function(t){}))}}};n.default=a},"64c9":function(t,n,i){"use strict";i.r(n);var e=i("7369"),a=i("2f99");for(var o in a)"default"!==o&&function(t){i.d(n,t,(function(){return a[t]}))}(o);i("dbf8");var u,c=i("f0c5"),f=Object(c["a"])(a["default"],e["b"],e["c"],!1,null,null,null,!1,e["a"],u);n["default"]=f.exports},7369:function(t,n,i){"use strict";var e;i.d(n,"b",(function(){return a})),i.d(n,"c",(function(){return o})),i.d(n,"a",(function(){return e}));var a=function(){var t=this,n=t.$createElement;t._self._c},o=[]},af8b:function(t,n,i){},dbf8:function(t,n,i){"use strict";var e=i("af8b"),a=i.n(e);a.a}}]);
;(global["webpackJsonp"] = global["webpackJsonp"] || []).push([
    'pages/index/diy/components/liveBroadcast-create-component',
    {
        'pages/index/diy/components/liveBroadcast-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('543d')['createComponent'](__webpack_require__("64c9"))
        })
    },
    [['pages/index/diy/components/liveBroadcast-create-component']]
]);
