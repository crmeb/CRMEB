{extend name="public/container"}
{block name="title"}我的推广人{/block}
{block name="content"}
<style>
    .con-cell{position: relative;}
    .con-cell div{  position: absolute; right: .2rem; margin-top: -.24rem; border: 1px solid #9E9E9E; padding: .1rem; border-radius: 3px; color: #666; top: 50%;}
</style>
<div id="spread-list" class="receive-pack">
    <section>
        <div class="statistics-count" ref="head">
            <span class="receive-pack-icon"></span>
            <span>推荐人统计：{$total}</span>
        </div>
        <div class="receive-pack-list" ref="bsDom">
            <div style="-webkit-overflow-scrolling : touch; position: relative;">
                <ul>
                    <li class="clearfix" v-for="item in group.list" v-cloak="">
                        <div class="avatar fl"><img :src="item.avatar"></div>
                        <div class="peo-info fl">
                            <div class="con-cell">
                                <div><a :href="'{:url('my/commission')}?uid='+ item.uid">查看佣金</a></div>
                                <p v-text="item.nickname"></p>
                                <span>关注时间: {{item.add_time}}</span><span></span>
                            </div>
                        </div>
                    </li>
                </ul>
                <p class="loading-line" v-show="loading == true"><i></i><span>正在加载中</span><i></i></p>
                <p class="loading-line" v-show="loading == false && group.loaded == false" v-cloak=""><i></i><span>加载更多</span><i></i></p>
                <p class="loading-line" v-show="loading == false && group.loaded == true" v-cloak=""><i></i><span>没有更多了</span><i></i></p>
            </div>

        </div>
    </section>
</div>
<script>
    (function(){
        require(['vue','axios','better-scroll','helper','store'],function(Vue,axios,BScroll,$h,storeApi){
            new Vue({
                el:"#spread-list",
                data:{
                    group:{
                        first:0,
                        limit:20,
                        list:[],
                        loaded:false,
                        top:0
                    },
                    loading: false,
                    scroll:null,
                },
                methods:{
                    getList:function(){
                        if(this.loading) return ;
                        var that = this,type = 'group',group = that.group;
                        if(group.loaded) return ;
                        this.loading = true;
                        storeApi.getSpreadList({
                            first:group.first,
                            limit:group.limit
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
                            this.$refs.head.offsetHeight)+'px';
                        this.$refs.bsDom.style.overflow = 'hidden';

                        this.scroll = new BScroll(this.$refs.bsDom,{useTransition:false,click:true,probeType:1,cancelable:false,deceleration:0.005,snapThreshold:0.01});
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
        })
    })();
</script>
{/block}