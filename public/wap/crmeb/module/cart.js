requirejs(['vue','store','helper'],function(Vue,storeApi,$h){
    new Vue({
        el:"#store-cart",
        data:{
            validCartList:[],
            invalidCartList:[],
            totalPrice:0,
            checkedAll:true,
            changeStatus:false,
            loading:false
        },
        watch:{
            validCartList:{
                handler:function(){
                    this.getTotalPrice();
                },
                deep:true
            }
        },
        methods:{
            cartNumTotal:function(){
                return this.validCartList.reduce(function(total,cart){
                    return  total+=cart.cart_num;
                },0);
            },
            getStoreUrl:function (cart) {
                return $h.U({
                    c:'store',
                    a:'detail',
                    p:{id:cart.productInfo.id}
                });
            },
            cartCount:function(){
                return this.getCheckedCart().reduce(function(total,cart){
                    return total+=cart.cart_num;
                },0);
            },
            checkedAllCart:function(){
                var that = this;
                var validCartList = this.validCartList.map(function(cart){
                    if(cart.is_del !== true) cart.checked = that.checkedAll;
                });
            },
            checkedCart:function(cart){
                this.checkedAllStatus();
            },
            checkedAllStatus:function(){
                this.checkedAll = this.validCartList.length > 0 && this.getCheckedCart().length == this.validCartList.length;
            },
            getCheckedCart:function(){
                return this.validCartList.filter(function(cart){
                    return cart.is_del != true && cart.checked == true;
                });
            },
            getTotalPrice:function(){
                this.totalPrice = this.getCheckedCart().reduce(function(total,cart){
                    return $h.Add(total,$h.Mul(cart.cart_num,cart.truePrice));
                },0);

            },
            getCartList:function(){
                var that = this;
                storeApi.getCartList(function(cartGroup){
                    cartGroup.valid.map(function(cart){
                        cart.checked = true;
                        cart.is_del = false;
                    });
                    that.checkedAll = cartGroup.valid.length > 0;
                    that.validCartList = cartGroup.valid;
                    that.invalidCartList = cartGroup.invalid;
                    that.loading = true;
                });
            },
            getAttrValues:function (cart) {
                return cart.productInfo.attrInfo == undefined ? '' : '属性:'+cart.productInfo.attrInfo.suk;
            },
            changeCartNum:function(cart,index,changeNum){
                var num = +cart.cart_num + changeNum;
                if(num <= 0) num = 1;
                if(num > cart.trueStock){
                    $h.pushMsgOnce('该商品库存不足'+num);
                    num = cart.trueStock;
                }
                if(cart.cart_num != num){
                    storeApi.changeCartNum(cart.id,num);
                    cart.cart_num = num;
                    this.$set(this.validCartList,index,cart);
                }
            },
            removeCart:function(){
                var ids = [],validCartList = [];
                this.validCartList.map(function(cart){
                    if(cart.checked){
                        cart.is_del = true;
                        ids.push(cart.id);
                    }else{
                        validCartList.push(cart);
                    }
                });
                if(ids.length) storeApi.removeCart(ids);
                this.$set(this,'validCartList',validCartList);
                this.$nextTick(function(){
                    this.checkedAllStatus();
                    this.changeStatus = false;
                });
            },
            submitCart:function(){
                var ids = this.getCheckedCart().map(function(cart){
                    return cart.id;
                });
                if(!ids.length) return false;
                location.href = $h.U({
                    c:'store',
                    a:'confirm_order',
                    p:{cartId:ids}
                });
            },
            removeInvalidCart:function(cart,index){
                storeApi.removeCart([cart.id]);
                this.invalidCartList.splice(index,1);
            }
        },
        mounted:function(){
            this.getCartList();
        }
    })
});