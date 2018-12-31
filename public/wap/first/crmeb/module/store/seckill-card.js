(function (global,factory) {
    typeof define && define.amd && define(['vue'],factory);
})(this,function(Vue){
    var template = '<div class=shop-card>' +
        '<div class=model-bg :class="{on:show == true}" @click=close @touchmove.prevent>' +
        '</div>' +
        '<div class=card-model :class="{up:show == true}">' +
        '<div class=cm-header @touchmove.prevent>' +
        '<div class=header-product>' +
        '<img :src=product.image>' +
        '</div>' +
        '<div class=header-info>' +
        '<p class=money>' +
        '￥<i v-text=product.price></i>' +
        '</p>' +
        '<span class=stock>' +
        '库存<i v-text=product.stock></i>' +
        '件</span>' +
        '</div>' +
        '</div>' +
        '<div class="amount clearfix" @touchmove.prevent>' +
        '<div class="text fl">购买数量</div>' +
        '<div class="count fr">' +
        '<div @click=descCartNum>-</div>' +
        '<input type=number v-model=cartNum readonly disabled style="-webkit-text-fill-color: #666;-webkit-opacity:1; opacity: 1;">' +
        '<div @click=incCartNum>+</div>' +
        '</div>' +
        '</div>' +
        '<div class=model-footer @touchmove.prevent>' +
        '<a class=footer-buy href="javascript:void(0);" v-if="buy == true" v-show="product.stock > 0" @click=goBuy>' +
        '立即秒杀' +
        '</a>' +
        '<a class="footer-buy no" href="javascript:void(0);" v-show="!product.stock || product.stock <= 0">' +
        '无货' +
        '</a>' +
        '</div>' +
        '<div class=model-close @click=close>' +
        '</div>' +
        '</div>' +
        '</div>';

    var shopCard = Vue.extend({
        template:template,
        props:{
            onShow:Function,
            onClose:Function,
            onChange:Function,
            onBuy:Function,
            onCart:Function,
            cart:{
                type:Boolean,
                default:true
            },
            buy:{
                type:Boolean,
                default:true
            },
            product:{
                type:Object,
                default:function(){
                    return {
                        image:'',
                        price:'',
                        stock:0
                    }
                }
            },
            attrList:{
                type:Array,
                default:function (){
                    return [];
                }
            },
            show:Boolean
        },
        data:function(){
            return {
                cartNum:1
            }
        },
        watch:{
            cartNum:function(v){
                if(this.product.stock <= 0) this.cartNum = 0;
                else if(v > this.product.stock) this.cartNum = this.product.stock;
                else if(v < 1) this.cartNum = 1;
            },
            attrList:{
                handler:function (v) {
                    this.$nextTick(function(){
                        this.onChange && this.onChange(this.getCheckedValue());
                    })
                },
                deep:true
            },
            product:function(){
                this.cartNum = 1;
            }
        },
        methods:{
            changeProduct:function(product){
                this.product = product;
            },
            getCheckedValue:function(){
                return this.attrList.map(function(attr){
                    return attr.checked;
                });
            },
            close:function(){
                this.show = false;
                this.onClose && this.onClose();
            },
            active:function(){
                this.show = true;
                this.onShow && this.onShow();
            },
            incCartNum:function(){
                if(this.product.num <= this.cartNum){
                    $h.pushMsg('每次购买数量不能超过'+this.product.num+'个')
                    this.cartNum = this.product.num;
                }else{
                    this.cartNum+=1;
                }
            },
            descCartNum:function(){
                this.cartNum-=1;
            },
            changeChecked:function(value,attr,index){
                attr.checked = value;
                this.$set(this.attrList,index,attr);
            },
            goCart:function(){
                if(this.cartNum > this.product.stock || this.cartNum <= 0) return false;
                this.onCart && this.onCart(this.getCheckedValue(),this.cartNum,this.product) == true && this.init();
            },
            goBuy:function(){
                if(this.cartNum > this.product.stock || this.cartNum <= 0) return false;
                this.onBuy && this.onBuy(this.getCheckedValue(),this.cartNum,this.product) == true && this.init();
            },
            init:function(){
                var that = this;
                this.attrList.map(function(attr,index){
                    attr.checked = attr.attr_values[0];
                    that.$set(that.attrList,index,attr);
                });
                this.cartNum = this.product.stock >=1 ? 1 : 0;
            },
            _setData:function(opt){
                this.product = opt.product;
                this.attrList = opt.attrList == undefined ? [] : opt.attrList;
                this.onChange = opt.onChange;
                this.onClose = opt.onClose;
                this.onCart = opt.onCart;
                this.onBuy = opt.onBuy;
                this.cart = opt.cart == undefined ? true : opt.cart;
                this.buy = opt.buy == undefined ? true : opt.buy;
                this.init();
            }
        },
        mounted:function(){
            var that = this;
            this.init();
            this.$el.reload = function(){
                that.init();
            };

        }
    });

    shopCard.install = function(Vue){
        Vue.prototype.$shopCard = function(opt){
            var $vm = new shopCard().$mount(),$el = $vm.$el;
            document.body.appendChild($el);
            $vm._setData(opt);
            $vm.remove = function(){
                document.body.removeChild($el);
            };
            this.$nextTick(function(){
                setTimeout(function(){
                    opt.show == true && $vm.active();
                },0);
            });
            return $vm;
        };
    };

    return shopCard;
});