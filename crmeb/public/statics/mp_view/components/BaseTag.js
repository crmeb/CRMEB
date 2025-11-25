(global["webpackJsonp"]=global["webpackJsonp"]||[]).push([["components/BaseTag"],{"0fb5":function(t,e,n){"use strict";n.r(e);var r=n("7aa3"),o=n.n(r);for(var a in r)["default"].indexOf(a)<0&&function(t){n.d(e,t,(function(){return r[t]}))}(a);e["default"]=o.a},31068:function(t,e,n){"use strict";n.r(e);var r=n("eba8"),o=n("0fb5");for(var a in o)["default"].indexOf(a)<0&&function(t){n.d(e,t,(function(){return o[t]}))}(a);n("edce");var i=n("828b"),u=Object(i["a"])(o["default"],r["b"],r["c"],!1,null,null,null,!1,r["a"],void 0);e["default"]=u.exports},7942:function(t,e,n){},"7aa3":function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var r={name:"tag",props:{size:{type:String,default:"normal"},text:{type:String,default:""},circle:{type:[Boolean,String],default:!1},background:{type:String,default:"#e93323"},color:{type:String,default:"#ffffff"},borderColor:{type:String,default:""},imgSrc:{type:String,default:""}},computed:{tagStyle:function(){return{background:this.background,color:this.color,border:this.borderColor?"1rpx solid ".concat(this.borderColor):"none"}}}};e.default=r},eba8:function(t,e,n){"use strict";n.d(e,"b",(function(){return r})),n.d(e,"c",(function(){return o})),n.d(e,"a",(function(){}));var r=function(){var t=this.$createElement,e=(this._self._c,this.imgSrc?null:this.__get_style([this.tagStyle]));this.$mp.data=Object.assign({},{$root:{s0:e}})},o=[]},edce:function(t,e,n){"use strict";var r=n("7942"),o=n.n(r);o.a}}]);
;(global["webpackJsonp"] = global["webpackJsonp"] || []).push([
    'components/BaseTag-create-component',
    {
        'components/BaseTag-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('df3c')['createComponent'](__webpack_require__("31068"))
        })
    },
    [['components/BaseTag-create-component']]
]);
