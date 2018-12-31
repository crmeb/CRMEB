{extend name="public/container"}
{block name="title"}评价列表{/block}
{block name="content"}
<body style="background:#f5f5f5;">
<div id="product-reply" class="user-evaluation-list">
    <section>
        <div class="menu-wrapper border-1px" ref="head">
            <p class="title">宝贝评价({$replyCount})</p>
            <div class="menu-list flex">
                <a class="item" href="javascript:void(0);" :class="{active:type == 'all'}" @click="type = 'all'">全部</a>
                <a class="item" href="javascript:void(0);" :class="{active:type == 'new'}" @click="type = 'new'">最新</a>
                {egt name="picReplyCount" value="1"}
                <a class="item" href="javascript:void(0);" :class="{active:type == 'pic'}" @click="type = 'pic'">有图({$picReplyCount})</a>
                {/egt}
            </div>
        </div>
        <div class="evaluation-list" ref="bsDom">
            <div>
            <ul>
                <li class="border-1px border-top" v-for="item in group.list" v-cloak="">
                    <div class="user-name flex">
                        <div class="avatar"><img :src="item.avatar"/></div>
                        <div class="name" v-text="item.nickname"></div>
                        <div class="star" :class="'star'+item.star"><span class="num"></span></div>
                    </div>
                    <div class="time"><span v-text="item.add_time"></span><span v-text="item.suk"></span></div>
                    <div class="text-con" v-text="item.comment"></div>
                    <div class="picture-list flex">
                        <img v-for="pic in item.pics" :src="pic" @click="showPic(pic,item.pics)"/>
                    </div>
                    <div class="reply" v-show="item.merchant_reply_content.length > 0" v-text="'店铺回复：'+item.merchant_reply_content">
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
        require(['vue','better-scroll','store'],function(Vue,BScroll,storeApi){
            var wxApi = mapleWx($jssdk());
            new Vue({
                el:"#product-reply",
                data:{
                    group:{
                        first:0,
                        limit:20,
                        list:[],
                        loaded:false
                    },
                    type:'all',
                    loading: false,
                    scroll:null,
                    productId:"<?=$productId?>"
                },
                watch:{
                    type:function(){
                        this.group = {
                            first:0,
                            limit:20,
                            list:[],
                            loaded:false
                        };
                        this.loading = false;
                        this.getList();
                    }
                },
                methods:{
                    showPic:function(pic,pics){
                        pics.forEach(function(v,k){
                            pics[k] = location.origin+v;
                        });
                        wxApi.previewImage(location.origin+pic,pics);
                    },
                    getList:function(){
                        if(this.loading) return ;
                        var that = this,type = 'group',group = that.group;
                        if(group.loaded) return ;
                        this.loading = true;
                        storeApi.getProductReply({
                            first:group.first,
                            limit:group.limit,
                            filter:this.type,
                            productId:this.productId
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
                                if(!groupLength)setTimeout(function(){that.scroll.scrollTo(0,0,300);},0);
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