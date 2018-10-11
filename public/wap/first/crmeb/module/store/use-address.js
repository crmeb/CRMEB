(function(global,factory){
    typeof define == 'function' && define(['store','helper'],factory);
})(this,function(storeApi,$h){
    var template = '<div><div><div class="model-bg":class="{on:show == true}" @touchmove.prevent @click="close"></div><div class="card-model addres-select":class="{up:show == true}"><div class="cm-header">收货地址</div><div class="empty" v-show="addressList.length == 0"  @touchmove.prevent> <img src="/pubilc/wap/first/crmeb/images/empty_address.png"><p>&nbsp;</p></div><div id="selects-wrapper"class="selects-info"><ul><li class="flex"v-for="item in addressList":class="{on:checkedAddressId == item.id}"@click="selectAddress(item)"><div class="select-icon"></div><div class="user-info"><p><span class="name"v-text="item.real_name"> </span> <span class="tel"v-text="item.phone"></span></p><p class="address-info"v-text="addressText(item)"></p></div><a class="edit"@click="goEdit(item.id)"href="javascript:void(0);"></a></li></ul></div><div class="model-footer"><a class="footer-buy"href="javascript:void(0);" @click="goAdd">添加新地址</a></div></div></div></div>';

    return {
        factory:function(Vue){
            return Vue.extend({
                template:template,
                props:{
                    checkedAddressId:{
                        type:Number,
                        default:function(){return 0;}
                    },
                    onSelect:{
                        type:Function
                    },
                    onClose:{
                        type:Function
                    },
                    onShow:{
                        type:Function
                    },
                    show:Boolean
                },
                data:function(){
                    return {
                        addressList:[]
                    }
                },
                methods:{
                    goEdit:function(addressId){
                        location.href = $h.U({
                            c:'my',
                            a:'edit_address',
                            p:{addressId:addressId}
                        });
                    },
                    goAdd:function(){
                        location.href = $h.U({
                            c:'my',
                            a:'edit_address'
                        });
                    },
                    getUserAddress:function(){
                        var that = this;
                        storeApi.getUserAddress(function(res){
                            that.addressList = res.data.data;
                        });
                    },
                    addressText:function(address){
                        return address.province+address.city+address.district+address.detail
                    },
                    close:function(){
                        this.show = false;
                        this.onClose && this.onClose();
                    },
                    remove:function(){
                        var that = this;
                        setTimeout(function(){
                            that.$el.remove();
                        },600);
                    },
                    active:function(){
                        this.show = true;
                        this.onShow && this.onShow();
                    },
                    selectAddress:function(address){
                        this.close();
                        this.onSelect && this.onSelect(address.id,address);
                    },
                    init:function(opt){
                        if(!opt) opt = {};
                        if(typeof opt.onClose == 'function') this.onClose = opt.onClose;
                        if(typeof opt.onSelect == 'function') this.onSelect = opt.onSelect;
                        if(typeof opt.onShow == 'function') this.onShow = opt.onShow;
                        if(opt.checked != undefined) this.checkedAddressId = opt.checked;
                    }
                },
                mounted:function(){
                    vm = this;
                    this.getUserAddress();
                }
            });
        },
        install:function(Vue){
            var useAddress = this.factory(Vue);
            var $vm = new useAddress().$mount(),$el = $vm.$el;
            document.body.appendChild($el);
            Vue.prototype.$useAddress = function(opt){
                $vm.init(opt);
                $vm.active();
            };
        }
    }
});