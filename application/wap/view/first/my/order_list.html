{extend name="public/container"}
{block name="title"}订单列表{/block}
{block name="content"}
<style>
    .user-order-list .menu .item span{padding: 0;}
</style>
<div class="user-order-list" id="order-list">
    <section>
        <form action="" method="post" @submit.prevent="searchOrder">
            <div class="search-wrapper flex" ref="head">
                <input type="search" v-model="search" placeholder="输入订单号"/>
                <button type="button" @click="searchOrder"><i class="iconfont icon-icon"></i></button>
            </div>
            <div class="menu flex" ref="nav">
                <div class="item" :class="{on:type === ''}" @click="changeType('')"><span>全部</span></div>
                <div class="item" :class="{on:type === 0}" @click="changeType(0)"><span>待付款</span></div>
                <div class="item" :class="{on:type == 11}" @click="changeType(11)"><span>拼团中</span></div>
                <div class="item" :class="{on:type == 1}" @click="changeType(1)"><span>待发货</span></div>
                <div class="item" :class="{on:type == 2}" @click="changeType(2)"><span>待收货</span></div>
                <div class="item" :class="{on:type == 3}" @click="changeType(3)"><span>待评价</span></div>
            </div>
            <div class="user-order-con" ref="bsDom" style="-webkit-overflow-scrolling : touch;">
                <div>
                <div class="item-block product-info" v-for="item in group.list" v-cloak="">
                    <div class="con-tit" @click="toOrderUrl(item)"><span class="count"><span v-if="item.combination_id != 0" style="background-color: #f48900;color: #fff;padding: .02rem .05rem;border-radius: 3px;font-size: 0.18rem;margin-right: .1rem;">拼团</span>订单: {{item.order_id}}</span> <span class="status-txt off fr" v-text="item._status._title"></span></div>
                    <div class="delivery-con" @click="toOrderUrl(item)">
                        <ul>
                            <li v-for="cart in item.cartInfo">
                                <a class="flex" href="javascript:void(0);">
                                    <div class="picture"><img @click.stop="toProductUrl(cart.productInfo.id)" :src="cart.productInfo.image" /></div>
                                    <div class="info-centent flex">
                                        <p class="name" v-text="cart.productInfo.store_name"></p>
                                        <p class="description" v-text="attrText(cart.productInfo)"></p>
                                        <p class="count"><span>￥{{cart.truePrice}}</span> X{{cart.cart_num}}</p>
                                    </div>
                                </a>
                                <div class="assess status-on" v-if="item._status._type == 3 && cart.is_reply == false">
                                    <a :href="'/wap/my/order_reply/unique/'+cart.unique">评价</a>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="all-price">
                        <div class="infos">
                            <p v-if="item.total_postage > 0">运费 <span class="fr">￥{{item.total_postage}}</span></p>
                            <p>商品总价 <span class="fr">￥{{item.total_price}}</span></p>
                            <p class="deb" v-show="item.coupon_price > 0">优惠 <span class="fr">-￥{{item.coupon_price}}</span></p>
                            <p class="deb" v-show="item.deduction_price > 0">积分抵扣 <span class="fr">-￥{{item.deduction_price}}</span></p>
                        </div>
                        <div class="all-count">实付款 <span class="fr">￥{{item.pay_price}}</span></div>
                        <div class="order-con-btn">
                            <a :href="'/wap/my/order_pink/id/'+item.pink_id" v-if="item.pink_id > 0"><span class="delete-order">查看拼团</span></a>
                            <a class="payment" v-if="item._status._type == 0" href="javascript:void(0);"  @click.prevent="confirm(item)">立即付款</a>
                            <a :href="'/wap/my/express/uni/'+item.order_id" v-if="item._status._type == 2 && item.delivery_type == 'express'"><span class="delete-order">查询快递</span></a>
                            <a class="payment" @click.prevent="userTake(item)" v-if="item._status._type == 2" href="javascript:void(0);">确认收货</a>
                            <a class="payment" @click.prevent="goDetails(item)" v-if="item._status._type == 3" href="javascript:void(0);">再来一单</a>
                        </div>

                    </div>
                </div>
                    <p class="loading-line" v-show="loading == true"><i></i><span>正在加载中</span><i></i></p>
                    <p class="loading-line" v-show="loading == false && group.loaded == false" v-cloak=""><i></i><span>加载更多</span><i></i></p>
                    <p class="loading-line" v-show="loading == false && group.loaded == true" v-cloak=""><i></i><span>没有更多了</span><i></i></p>
                </div>
            </div>
        </form>
    </section>
</div>
<script>
    (function(){
        require(['vue','axios','better-scroll','helper','store','layer'],function(Vue,axios,BScroll,$h,storeApi,layer){
            new Vue({
                el:"#order-list",
                data:{
                    group:{
                        first:0,
                        limit:8,
                        list:[],
                        loaded:false,
                        top:0
                    },
                    loading: false,
                    scroll:null,
                    type:null,
                    search:''
                },
                watch:{
                    type:function (v,o) {
                        if(v === null) return;
                        this.search = '';
                        this.group = {
                            first:0,
                            limit:8,
                            list:[],
                            loaded:false,
                            top:0
                        };
                        this.loading = false;
                        this.getList();
                        this.$nextTick(function(){
                            this.scroll.scrollTo(0,0);
                        });
                    }
                },
                methods:{
                    goDetails:function (item) {
                        storeApi.orderDetails(item.order_id,function(res){
                            if(res.data.code == 200){
                                var cartId = res.data.data;
                                location.href = $h.U({
                                    c: 'store',
                                    a: 'confirm_order',
                                    p: {cartId: cartId}
                                });
                            }else{
                                $h.pushMsg(res.data.msg);
                            }
                        },function(res){
                            $h.pushMsg(res.msg);
                        });
                    },
                    confirm:function(item){
                        var that = this;
                        if(this.payType == 'yue')
                            layer.confirm('确定使用余额支付?',{icon:3},function () {
                                that.goPay(item);
                            });
                        else if(this.payType == 'offline')
                            layer.confirm('确定使用线下付款方式支付?',{icon:3},function () {
                                that.goPay(item);
                            });
                        else
                            that.goPay(item);
                    },
                    goPay:function(item){
                        $h.loadFFF();
                        storeApi.payOrder(item.order_id,function(res){
                            $h.loadClear();
                            var data = res.data;
                            if(data.data.status == 'WECHAT_PAY'){
                                mapleWx($jssdk(),function(){
                                    this.chooseWXPay(data.data.result.jsConfig,function(){
                                        $h.pushMsg('支付成功',function(){
                                            location.reload(true);
                                        })
                                    },{
                                        fail:function(){ $h.pushMsg('已取消支付');},
                                        cancel:function(){ $h.pushMsg('已取消支付');}
                                    });
                                });
                            }else{
                                $h.pushMsg(data.msg,function(){
                                    location.reload(true);
                                });
                            }
                        },function(e){ $h.loadClear(); return true; })
                    },
                    userTake:function(item){
                        var that = this;
                        layer.confirm('确定立即收货?',{icon:3},function(index){
                            layer.close(index);
                            $h.loadFFF();
                            storeApi.userTakeOrder(item.order_id,function(){
                                $h.loadClear();
                                $h.pushMsg('收货成功',function(){
                                    location.reload(true);
                                });
                            },function(e){ $h.loadClear(); return true; });
                        })
                    },
                    searchOrder:function(){
                        if(this.search == '') return ;
                        this.group = {
                            first:0,
                            limit:8,
                            list:[],
                            loaded:false,
                            top:0
                        };
                        this.loading = false;
                        this.type = null;
                        this.getList();
                        this.$nextTick(function(){
                            this.scroll.scrollTo(0,0);
                        });
                    },
                    changeType:function(type){
                        if(this.loading) return ;
                        this.type = type;
                    },
                    attrText:function (product){
                        return product.attrInfo == undefined ? '' : product.attrInfo.suk;
                    },
                    toProductUrl:function(id){
                        location.href = $h.U({c:'store',a:'detail',p:{id:id}});
                    },
                    toOrderUrl:function(order){
                        location.href = $h.U({c:'my',a:'order',p:{uni:order.order_id}});
                    },
                    getList:function(){
                        if(this.loading) return ;
                        this.getOrderList();
                    },
                    getOrderList:function(){
                        var that = this,type = 'group',group = that.group;
                        if(group.loaded) return ;
                        this.loading = true;
                        storeApi.getUserOrderList({
                            type:this.type,
                            first:group.first,
                            limit:group.limit,
                            search:this.search
                        },function(res){
                            var list = res.data.data;
                            group.loaded = list.length < group.limit;
                            group.first += list.length;
                            group.list = group.list.concat(list);
                            that.$set(that,type,group);
                            that.loading = false;
                            that.$nextTick(function(){
                                if(list.length) that.scroll.refresh();
                                that.scroll.finishPullUp();
                            });
                        },function(){that.loading = false});
                    },
                    bScrollInit:function () {
                        var that = this;
                        this.$refs.bsDom.style.height = (
                            document.documentElement.clientHeight -
                            this.$refs.head.offsetHeight -
                            this.$refs.nav.offsetHeight)+'px';
                        this.$refs.bsDom.style.overflow = 'hidden';

                        this.scroll = new BScroll(this.$refs.bsDom,{click:true,probeType:1,cancelable:false,deceleration:0.005,snapThreshold:0.01});
                        this.scroll.on('pullingUp',function(){
                            that.loading == false && that.getList();
                        })
                    },
                    getType:function(){
                        if(location.hash == '')
                            this.type = '';
                        else if(location.hash == '#0')
                            this.type = 0;
                        else
                            this.type = location.hash.split('#')[1];
                    }
                },
                mounted:function(){
                    this.bScrollInit();
                    this.getType();
                }
            })
        })
    })();
</script>
{/block}