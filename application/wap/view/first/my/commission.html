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
    </section>
</div>
<script>

    (function(){
        var minRecharge = 0,uid = "{$uid}";
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

                        storeApi.baseGet($h.U({
                            c:"auth_api",
                            a:"get_user_brokerage_list",
                            p:{
                                uid:uid,
                                first:group.first,
                                limit:group.limit
                            }
                        }),function(res){
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