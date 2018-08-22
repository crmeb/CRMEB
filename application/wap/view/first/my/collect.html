{extend name="public/container"}
{block name="title"}已收藏商品{/block}
{block name="content"}
<body style="background:#fff;">
<div id="collect" class="user-collection-list">
    <section ref="bsDom">
        <div>
        <ul>
            <li class="border-1px" v-for="(item,index) in data.list" :class="{fail:item.is_fail == true}" v-cloak="">
                <a :href="productUrl(item)">
                    <div class="picture"><img :src="item.image" /></div>
                    <div class="info-content flex">
                        <p class="pro-tit" v-text="item.store_name"></p>
                        <p class="price">
                            <span v-text="'￥'+item.price"></span>
                            <span class="old-price">￥{{item.ot_price}}</span>
                        </p>
                        <p class="count">{{ item.is_fail == true ? '已失效' : '已售'+item.sales }}</p>
                    </div>
                </a>
                <div class="collection-delete" @click="removeCollect(item,index)"></div>
            </li>
        </ul>
            <p style="background-color: #fff;" class="loading-line" v-show="loading == true"><i></i><span>正在加载中</span><i></i></p>
            <p style="background-color: #fff;" class="loading-line" v-show="loading == false && data.loaded == false" v-cloak=""><i></i><span>加载更多</span><i></i></p>
            <p style="background-color: #fff;" class="loading-line" v-show="loading == false && data.loaded == true" v-cloak=""><i></i><span>没有更多了</span><i></i></p>
        </div>

    </section>
</div>
<script>
    require(['vue','axios','better-scroll','helper','store'],function(Vue,axios,BScroll,$h,storeApi){
        new Vue({
            el:"#collect",
            data:{
                data:{
                    first:0,
                    limit:8,
                    list:[],
                    loaded:false,
                    top:0
                },
                loading: false,
                scroll:null
            },
            methods:{
                removeCollect:function (product,index){
                    var that = this;
                    storeApi.removeCollectProduct(product.pid,function(){
                        that.data.list.splice(index,1);
                    });
                },
                productUrl:function(product){
                    return product.is_fail == true ? 'javascript:void(0);' : $h.U({c:'store',a:'detail',p:{id:product.pid}});
                },
                getList:function(){
                    if(this.loading) return ;
                    var that = this,group = that.data;
                    if(group.loaded) return ;
                    this.loading = true;
                    storeApi.getCollectProduct({
                        first:group.first,
                        limit:group.limit
                    },function(res){
                        var list = res.data.data;
                        group.loaded = list.length < group.limit;
                        group.first += list.length;
                        group.list = group.list.concat(list);
                        that.loading = false;
                        that.$set(that,'data',group);
                        that.$nextTick(function(){
                            if(list.length) that.bScrollInit();
                            that.scroll.finishPullUp();
                        });
                    },function(err){that.loading = false; return true;});
                },
                bScrollInit:function () {
                    var that = this;
                    if(this.scroll === null){
                        this.$refs.bsDom.style.height = (document.documentElement.clientHeight)+'px';
                        this.$refs.bsDom.style.overflow = 'hidden';
                        this.scroll = new BScroll(this.$refs.bsDom,{click:true,probeType:1,cancelable:false,deceleration:0.005,snapThreshold:0.01});
                        this.scroll.on('pullingUp',function(){
                            that.loading == false && that.getList();
                        })
                    }else{
                        this.scroll.refresh();
                    }

                }
            },
            mounted:function(){
                this.bScrollInit();
                this.getList();
            }
        })
    })
</script>
{/block}