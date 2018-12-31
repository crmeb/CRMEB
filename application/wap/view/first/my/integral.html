{extend name="public/container"}
{block name="title"}我的积分{/block}
{extend name="public/container"}
{block name="head"}
<style>
    .loading-line{background-color: #fff;}
</style>
{/block}
{block name="content"}
<div id="user-integral" class="integral-content">
    <section>
        <header ref="head">
            <div class="con-cell">
                <span>我的积分</span>
                <p>{$userInfo.integral|floatval}</p>
                <?php /*  <a href="">获取积分</a>  */ ?>
            </div>
        </header>
        <?php /*  <div class="entrance">
              <a href=""><i class="icon integral-mall"></i><span>进入积分商城</span></a>
              <a href=""><i class="icon address"></i><span>收货地址管理</span></a>
          </div>  */ ?>
        <div class="details">
            <div class="titles" ref="nav"><i class="icon details-icon"></i>收支明细</div>
            <div ref="bsDom">
                <div style="-webkit-overflow-scrolling : touch; position: relative;">
                    <ul>
                        <li class="clearfix" v-for="item in group.list" v-cloak="">
                            <div class="infos fl">
                                <div class="con-cell">
                                    <p v-text="item.mark"></p>
                                    <span v-text="item.add_time"></span>
                                </div>
                            </div>
                            <div class="count fr" :class="{increase:item.pm == 1}">
                                <div class="con-cell"><span>{{item.pm == 1 ? '+' : '-'}}{{item.number}}</span>积分</div>
                            </div>
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
        requirejs(['vue','helper','better-scroll','store'],function(Vue,$h,BScroll,storeApi){
            var wxApi = mapleWx($jssdk());
            new Vue({
                el:'#user-integral',
                data:{
                    group:{
                        first:0,
                        limit:20,
                        list:[],
                        loaded:false
                    },
                    loading: false,
                    scroll:null,
                },
                methods:{
                    getList:function(){
                        if(this.loading) return;
                        var that = this,type = 'group',group = that.group;
                        if(group.loaded) return ;
                        this.loading = true;
                        storeApi.getUserIntegralList({
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