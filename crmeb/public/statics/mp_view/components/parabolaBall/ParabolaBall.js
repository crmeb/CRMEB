(global["webpackJsonp"]=global["webpackJsonp"]||[]).push([["components/parabolaBall/ParabolaBall"],{"039e":function(t,e,n){"use strict";n.r(e);var r=n("39de"),a=n.n(r);for(var u in r)"default"!==u&&function(t){n.d(e,t,(function(){return r[t]}))}(u);e["default"]=a.a},"0a35":function(t,e,n){"use strict";n.r(e);var r=n("4356"),a=n("039e");for(var u in a)"default"!==u&&function(t){n.d(e,t,(function(){return a[t]}))}(u);var o,s=n("f0c5"),i=Object(s["a"])(a["default"],r["b"],r["c"],!1,null,null,null,!1,r["a"],o);e["default"]=i.exports},"39de":function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var r={props:{size:{type:Number,default:20},color:{type:String,default:"#f5222d"},zIndex:{type:Number,default:999},duration:{type:Number,default:500}},data:function(){return{dots:[]}},methods:{showBall:function(t){var e=this,n=t.start,r=(t.end,t.src);return new Promise((function(t){var a=e.dots.find((function(t){return!t.show}));a||(a={src:"",left:0,top:0,show:!1},e.dots.push(a));var u=e.duration,o=n.x-e.size/2,s=n.y-e.size/2,i=50-e.size/2,f=640-e.size/2,l=Date.now(),c=i-o,d=f-s,p=-2*c/(u*u)/5,v=Math.abs(p),b=c/u-p*u/2,h=d/u-v*u/2,w=function e(){var n=Date.now()-l,r=o+(b*n+p*n*n/2),i=s+(h*n+v*n*n/2);a.left=r,a.top=i,n<u?setTimeout(e):(a.show=!1,t())};a.src=r,a.show=!0,w()}))}}};e.default=r},4356:function(t,e,n){"use strict";var r;n.d(e,"b",(function(){return a})),n.d(e,"c",(function(){return u})),n.d(e,"a",(function(){return r}));var a=function(){var t=this,e=t.$createElement;t._self._c},u=[]}}]);
;(global["webpackJsonp"] = global["webpackJsonp"] || []).push([
    'components/parabolaBall/ParabolaBall-create-component',
    {
        'components/parabolaBall/ParabolaBall-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('543d')['createComponent'](__webpack_require__("0a35"))
        })
    },
    [['components/parabolaBall/ParabolaBall-create-component']]
]);
