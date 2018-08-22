{extend name="public/container"}
{block name="title"}我的账户{/block}
{extend name="public/container"}
{block name="head"}
<style>
    .loading-line{background-color: #fff;}
</style>
{/block}
{block name="content"}
<body style="background:#f5f5f5;">
<div id="user-balance" class="user-balance">
    <section>
        <header ref="head" @touchmove.prevent="">
            <div class="count-wrapper">
                <p>当前余额（元）</p>
                <span class="count-txt">{$userInfo.now_money}</span>
                <div class="text-bar"></div>
            </div>
            <div class="link-list">
                <ul>
                    <li class="border-1px">
                        <a href="javascript:void(0);" @click="showRechargeCard = true">
                            <i class="icon icon-money"></i>立即充值
                        </a>
                    </li>
                    <li>
                        <a href="{:url('index/index')}">
                            <i class="icon icon-mall"></i>进入商城
                        </a>
                    </li>
                </ul>
            </div>
        </header>
        <div class="list-info">
            <div class="link-list" ref="nav" @touchmove.prevent="">
                <ul>
                    <li class="border-1px"><i class="icon"></i>余额明细</li>
                </ul>
            </div>
            <div class="info-list" ref="bsDom">
                <div style="-webkit-overflow-scrolling : touch; position: relative;">
                <ul>
                    <li class="border-1px flex" v-for="item in group.list" v-cloak="">
                        <div class="txt-content">
                            <p v-text="item.mark"></p>
                            <span v-text="item.add_time"></span>
                        </div>
                        <div class="count" :class="{increase:item.pm == 1}">{{item.pm == 1 ? '+' : '-'}}{{item.number}}</div>
                    </li>
                </ul>
                    <p class="loading-line" v-show="loading == true"><i></i><span>正在加载中</span><i></i></p>
                    <p class="loading-line" v-show="loading == false && group.loaded == false" v-cloak=""><i></i><span>加载更多</span><i></i></p>
                    <p class="loading-line" v-show="loading == false && group.loaded == true" v-cloak=""><i></i><span>没有更多了</span><i></i></p>
                </div>
            </div>
        </div>
        <div class="model-bg" @touchmove.prevent @click="showRechargeCard = false" :class="{on:showRechargeCard == true}"></div>
        <div class="card-model" :class="{up:showRechargeCard == true}">
            <div id="selects-wrapper" class="selects-info" style="max-height: 4.96rem;">
                <div class="payplay-wrapper">
                    <div class="info-wrapper">
                        <div class="tit">输入充值金额</div>
                        <div class="money">
                            <span>￥</span>
                            <input v-model="rechargePrice" type="number" placeholder="0.00"/>
                        </div>
                        <div class="tips">充值提示：单次充值金额不能低于<span>{{minRecharge}}元</span></div>
                        <button class="pay-btn" @click="toRecharge">点击进行微信支付</button>
                    </div>
                </div>
            </div>
            <div class="model-close" @click="showRechargeCard = false"></div>
        </div>
    </section>
</div>
<script>
    (function(){
        var minRecharge = '<?=$userMinRecharge?>';
        requirejs(['vue','helper','better-scroll','store'],function(Vue,$h,BScroll,storeApi){
            var wxApi = mapleWx($jssdk());
            new Vue({
                el:'#user-balance',
                data:{
                    showRechargeCard:false,
                    minRecharge:minRecharge,
                    rechargePrice:'',
                    group:{
                        first:0,
                        limit:20,
                        list:[],
                        loaded:false
                    },
                    loading: false,
                    scroll:null,
                },
                watch:{
                    showRechargeCard:function(){
                        this.rechargePrice = '';
                    }
                },
                methods:{
                    getList:function(){
                        if(this.loading) return;
                        var that = this,type = 'group',group = that.group;
                        if(group.loaded) return ;
                        this.loading = true;
                        storeApi.getUserBalanceList({
                            first:group.first,
                            limit:group.limit
                        },function(res){
                            var list = res.data.data,groupLength = group.list.length;
                            that.scroll.stop();
                            group.loaded = list.length < group.limit;
                            group.first += list.length;
                            group.list = group.list.concat(list);
                            that.$set(that,type,group);
                            that.loading = false;
                            that.$nextTick(function(){
                                if(list.length || !groupLength) that.scroll.refresh();
                                if(!groupLength) setTimeout(function(){that.scroll.scrollTo(0,0,300);},0);
                                that.scroll.finishPullUp();
                            });
                        },function(){that.loading = false});
                    },
                    toRecharge:function(){
                        if(rechargePrice == '') return ;
                        var rechargePrice = parseFloat(this.rechargePrice);
                        if(rechargePrice != this.rechargePrice || rechargePrice <= 0)
                            return $h.pushMsgOnce('请输入正确的充值金额');
                        if(rechargePrice < minRecharge)
                            return $h.pushMsgOnce('充值金额不能低于'+parseFloat(minRecharge));
                        this.showRechargeCard = false;
                        this.rechargePrice = '';
                        storeApi.userWechatRecharge(rechargePrice,function(res){
                            wxApi.chooseWXPay(res.data.data,function(){
                                that.showRechargeCard = false;
                                $h.pushMsgOnce('成功充值'+rechargePrice);
                            });
                        });
                    },
                    bScrollInit:function () {
                        var that = this;
                        this.$refs.bsDom.style.height = (
                                document.documentElement.clientHeight -
                                this.$refs.head.offsetHeight -
                                this.$refs.nav.offsetHeight - 6.5
                            )+'px';
                        this.$refs.bsDom.style.overflow = 'hidden';
                        this.scroll = new BScroll(this.$refs.bsDom,{observeDOM:false,useTransition:false,click:true,probeType:1,cancelable:false,deceleration:0.005,snapThreshold:0.01});
                        this.scroll.on('pullingUp',function(){
                            that.loading == false && that.getList();
                        })
                    }
                },
                mounted:function(){
                    this.bScrollInit();
                    this.getList();
                }
            })
        });
    })();
</script>
{/block}