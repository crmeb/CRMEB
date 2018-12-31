(function(global,factory){
    typeof define == 'function' && define(['store'],factory);
})(this,function(storeApi){
    var template = '<div><div class="model-bg" @touchmove.prevent :class="{on:isShow == true}" @click="close"></div><div class="card-model" :class="{up:isShow == true}" style="height: 72%;"><div style="position: absolute;left: 0px;top: -2px;width: 100%;height: 0.7rem;background-color: rgb(255, 255, 255);z-index: 99;padding-left: .3rem;line-height: .7rem;">请选择优惠劵<div style="top: 50%;margin-top: -.2rem;" class="model-close" @click="close"></div></div><div class="empty" v-show="couponList.length == 0"  @touchmove.prevent> <img src="/public/wap/first/crmeb/images/empty_coupon.png"><p>暂无可用优惠劵</p></div><div v-show="couponList.length > 0" id="selects-wrapper" class="discount-list" style="height: 100%;    overflow-y: scroll;padding-top: .7rem;padding-bottom: .2rem;-webkit-overflow-scrolling : touch; " @touchmove.stop><div style="margin-top: 0" class="list-box"><ul><li class="new" :class="{use:coupon._type == 0 || minPrice < coupon.use_min_price}" v-for="coupon in couponList" @click="select(coupon)"><div class="txt-info"><div class="con-cell"><p>满{{coupon.use_min_price}}元可用现金券</p><span>{{coupon._add_time}}至{{coupon._end_time}}使用</span></div></div><div class="price"><span>￥</span>{{coupon.coupon_price}}<p><a href="javascript:void(0);" v-if="coupon._type == 0">{{coupon._msg}}</a><a href="javascript:void(0);" v-if="coupon._type != 0">{{minPrice< coupon.use_min_price ? "无法使用" : "立即使用"}}</a></p></div><span class="text-icon" v-show="coupon._type == 2"></span></li></ul></div></div></div></div>';

    return {
        factory:function(Vue){
            return Vue.extend({
                name:'use-coupon',
                template:template,
                props:{
                    onClose:Function,
                    onSelect:Function,
                    onShow:Function,
                },
                data:function(){
                    return {
                        couponList:[],
                        isShow:false,
                        minPrice:0
                    };
                },
                methods:{
                    getCouponList:function(){
                        var that = this;
                        storeApi.getUseCoupon(function(res){
                            that.couponList = res.data.data;
                        });
                    },
                    select:function(coupon){
                        if(this.minPrice < coupon.use_min_price) return ;
                        this.onSelect && this.onSelect(coupon);
                        this.close();
                    },
                    active:function(){
                        this.isShow = true;
                        this.onShow && this.onShow();
                    },
                    close:function(){
                        this.isShow = false;
                        this.onClose && this.onClose();
                    },
                    init:function(minPrice,opt){
                        if(!opt) opt = {};
                        if(!minPrice) minPrice = 0;
                        if(typeof opt.onClose == 'function') this.onClose = opt.onClose;
                        if(typeof opt.onSelect == 'function') this.onSelect = opt.onSelect;
                        if(typeof opt.onShow == 'function') this.onShow = opt.onShow;
                        this.minPrice = minPrice;
                    }
                },
                mounted:function(){
                    this.getCouponList();
                }
            });
        },
        install:function(Vue){
            var that = this;
            Vue.prototype.$useCoupon = function(minPrice,opt){
                var useCoupon = that.factory(Vue);
                var $vm = new useCoupon().$mount(),$el = $vm.$el;
                document.body.appendChild($el);
                $vm.init(minPrice,opt);
                $vm.remove = function(){
                    setTimeout(function(){
                        document.body.removeChild($el);
                    },600);
                };
                return {
                    remove:$vm.remove,
                    active:$vm.active,
                    close:$vm.close
                };
            };
        }
    }
});