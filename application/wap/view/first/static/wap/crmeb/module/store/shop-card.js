(function (global,factory) {
    typeof define && define.amd && define(['vue'],factory);
})(this,function(Vue){
    var template = '<div class=shop-card><div class=model-bg :class="{on:show == true}" @click=close @touchmove.prevent></div><div class=card-model :class="{up:show == true}"><div class=cm-header @touchmove.prevent><div class=header-product><img :src=product.image></div><div class=header-info><p class=money>￥<i v-text=product.price></i></p><span class=stock>库存<i v-text=product.stock></i>件</span><p class=tips v-show="attrList.length > 0">请选择属性</p></div></div><div id=selects-wrapper class=selects-info v-show="attrList && attrList.length > 0"><div class=scroll><div class=cm-selects v-for="(attr,index) in attrList"><h1>{{attr.attr_name}}</h1><div class="options option-color"><span v-for="value in attr.attr_values" :class="{on:attr.checked == value}" @click="changeChecked(value,attr,index)">{{value}}</span></div></div></div></div><div class="amount clearfix" @touchmove.prevent><div class="text fl">购买数量</div><div class="count fr"><div @click=descCartNum>-</div><input type=number v-model=cartNum readonly><div @click=incCartNum>+</div></div></div><div class=model-footer @touchmove.prevent><a class=footer-car href="javascript:void(0);" v-if="cart == true" v-show="product.stock > 0" @click=goCart>加入购物车</a><a class=footer-buy href="javascript:void(0);" v-if="buy == true" v-show="product.stock > 0" @click=goBuy>立即购买</a><a class="footer-buy no" href="javascript:void(0);" v-show="!product.stock || product.stock <= 0">无货</a></div><div class=model-close @click=close></div></div></div>';

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
                this.cartNum+=1;
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
            setTimeout(function(){
                opt.show == true && $vm.active();
            },300);
            return $vm;
        };
    };
    
    return shopCard;
});