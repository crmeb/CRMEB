(function(global,factory){
    typeof define == 'function' && define([],factory);
})(this,function() {
    return {
        style:'.tmp-wrapper .tmp-container{z-index:99;position:fixed;left:0;bottom:0;width:100%;background-color:#fff;-webkit-transform:translate3d(0,110%,0);transform:translate3d(0,110%,0);-webkit-transition:all .5s ease;transition:all .5s ease}.tmp-wrapper .tmp-container .title{height: 1.06rem;line-height: 1.06rem;color:#1d1d1d;font-size:.26rem;text-align:center;border-bottom:1px solid #eee}.tmp-wrapper .tmp-container .title span{color: #666;font-size: .2rem;}.tmp-wrapper .tmp-container .tmp-main .item{position:relative;padding:0 .2rem;height:.75rem;border-bottom:1px solid #eee;-webkit-box-pack:justify;-o-box-pack:justify;-ms-flex-pack:justify;justify-content:space-between;-webkit-box-align:center;-o-box-align:center;-ms-flex-align:center;align-items:center}.tmp-wrapper .tmp-container .tmp-main .item .input-box{width:4.2rem}.tmp-wrapper .tmp-container .tmp-main .item .input-box input::-webkit-input-placeholder{color:#ccc}.tmp-wrapper .tmp-container .tmp-main .item .address-icon{position:absolute;right:.2rem;top:50%;display:inline-block;width:.25rem;height:.35rem;margin-top:-.175rem;background-image:url(../images/add-icon.png);background-size:100% 100%}.tmp-wrapper .tmp-container .tmp-main .item .selected{position:relative;height:100%}.tmp-wrapper .tmp-container .tmp-main .item .selected input{display:none}.tmp-wrapper .tmp-container .tmp-main .item .selected .icon{position:absolute;left:-.16rem;top:50%;margin:-.18rem 0 0 -.18rem;display:inline-block;width:18px;height:18px;border:1px solid #cdcdcd;border-radius:50%}.tmp-wrapper .tmp-container .submit-btn{width:100%;height:.77rem;margin-top:.3rem}.tmp-wrapper .tmp-container .submit-btn button{display:block;width:100%;height:100%;color:#fff;background-color:#e3383e}.tmp-wrapper .tmp-container.up{-webkit-transform:translate3d(0,0,0);transform:translate3d(0,0,0)}.tmp-container .tmp-main .item.on{background-image: url(/public/wap/crmeb/images/enter01.png);color: #e52800;background-size: .22rem auto;background-repeat: no-repeat;background-position: 95% center;}.tmp-wrapper .close{position: absolute;right: .2rem;top: .2rem;background-image: url(/public/wap/sx/images/mtw-close.png);width: .3rem;height: .3rem;background-size: 100% 100%;}',
        template:'<div class="refund-reason tmp-wrapper" v-cloak=""><div class="model-bg" :class="{on:show == true}" @click="show = false" @touchmove.prevent></div><div class="tmp-container" :class="{up:show == true}" @touchmove.prevent><div class="title"><p>请选择退款原因</p></div><div class="tmp-main"><div class="item flex" :class="{on:selectIndex == index}" v-for="(msg,index) in reason" @click="selectIndex = index"><span>{{msg}}</span></div></div><div class="submit-btn"><button type="submit" @click="select">提交</button></div><div class="close" @click="show = false"></div></div></div>',
        install:function (Vue) {
            this.factory(Vue);
        },
        factory:function (Vue) {
            var that = this;
            $f = Vue.extend({
                template:this.template,
                data:function () {
                    return {
                        show:false,
                        reason:[
                            '收货地址填错了',
                            '大小尺寸与描述不符',
                            '颜色、款式、图案描述不符合',
                            '尺寸拍错不喜欢、效果不好',
                            '收到商品破损',
                            '未按约定时间发货',
                            '商品质量问题'
                        ],
                        selectIndex:-1,
                        selectFn:function(){}
                    };
                },
                watch:{
                    show:function (n) {
                        if(!n)this.selectIndex = -1;
                    }
                },
                methods:{
                    select:function () {
                        if(this.selectIndex < 0) return false;
                        this.selectFn && this.selectFn(this.reason[this.selectIndex]);
                        this.show = false;
                    }
                }
            });
            $vm = new $f().$mount();
            document.body.appendChild($vm.$el);
            var styleDom = document.createElement('style');
            styleDom.innerHTML = that.style;
            document.head.appendChild(styleDom);
            Vue.prototype.$orderRefundReason = function (opt) {
                $vm.show = true;
                if(opt !== undefined) $vm.selectFn = opt;
            }
        }
    }
});
