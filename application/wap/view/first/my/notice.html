{extend name="public/container"}
{block name="title"}消息通知{/block}
{block name="content"}
   <div class="text-list" id="notice-list" v-cloak="">
        <section ref="bsDom">
            <ul style="-webkit-overflow-scrolling : touch;">
                <li v-for="item in noticelist.lists" :nid="item.id">
                    <div class="admin">
                        <span class="icon" v-if="item.is_see == 0"></span>
                        {{item.user}}
                        <span class="add_time">{{item.add_time}}</span>
                    </div>
                    <div class="text-wrapper">
                        <div class="title">{{item.title}}</div>
                        <div class="text-box">{{item.content}}</div>
                    </div>
                    <div class="link-more">
                        <span class="more-btn" @click="seeNotice">查看全文>></span>
                    </div>
                </li>
                <p class="loading-line" v-show="noticelist.loading == 1"><i></i><span>正在加载中</span><i></i></p>
                <p class="loading-line" v-show="noticelist.loading == 0 && noticelist.lastpage == 0"><i></i><span>加载更多</span><i></i></p>
                <p class="loading-line" v-show="noticelist.loading == 0 && noticelist.lastpage == 1"><i></i><span>没有更多了</span><i></i></p>
            </ul>
        </section>
    </div>
    <script type="text/javascript">
    (function(){
        require(['vue','axios','better-scroll','helper','store'],function(Vue,axios,BScroll,$h,storeApi){
            new Vue({
                el:"#notice-list",
                data:{
                    noticelist: {lists: [],page: 0,lastpage: 0,loading: 0,limit:8},
                    scroll:null
                },
                methods: {
                    initNoticeList: function(){
                        this.noticelist.lists = [];
                        this.noticelist.page = 0;
                        this.noticelist.lastpage = 0;
                        this.noticelist.loading = 0;
                        this.getNoticeList();
                    },
                    getNoticeList: function(){
                        var that = this;
                        if(that.noticelist.loading || that.noticelist.lastpage)return;
                        that.noticelist.loading = 1;//开启加载开关
                        storeApi.getNoticeList({
                            page:that.noticelist.page,
                            limit:that.noticelist.limit
                        },function(res){
                            that.noticelist.loading = 0;
                            if(res.data.code == 200){
                                var re_data = res.data.data;
                                that.noticelist.lastpage = re_data.lastpage;
                                that.noticelist.lists = that.noticelist.lists.concat(re_data.list);
                                if(!re_data.lastpage)that.noticelist.page++;//如果不是最后一页当前页码加1
                                that.$nextTick(function(){
                                    that.scroll.refresh();
                                    that.scroll.finishPullUp();
                                });
                            }
                        },function(){that.noticelist.loading = false});
                    },
                    bScrollInit: function(){
                        var that = this;
                        this.$refs.bsDom.style.height = document.documentElement.clientHeight +'px';
                        this.$refs.bsDom.style.overflow = 'hidden';
                        this.scroll = new BScroll(this.$refs.bsDom,{click:true,probeType:1,cancelable:false,deceleration:0.005,snapThreshold:0.01});
                        this.scroll.on('pullingUp',function(){
                            that.noticelist.loading == 0 && that.getNoticeList();
                        })
                    },
                    seeNotice: function(event){
                        var element = $(event.target).parents("li");
                        if(element.find(".admin .icon").length > 0){
                            storeApi.seeNotice({
                                nid:element.attr("nid")
                            },function(res){
                                if(res.data.code == 200)element.find(".admin .icon").remove();
                            },function(){ $h.loadClear(); });

                            this.initText(element);
                        }else{
                            this.initText(element);
                        }
                    },
                    initText: function(element){
                        var status = element.find(".text-box").hasClass('active');
                        if(status){
                            element.find(".text-box").removeClass("active");
                            $(event.target).text("查看全文>>");
                            $(event.target).css("color","#fc641c");
                            this.scroll.refresh();
                        }else{
                           element.find(".text-box").addClass('active');
                           $(event.target).text("点击收起>>");
                           $(event.target).css("color","#1cb0fc");
                           this.scroll.refresh();
                        }
                    }
                },
                mounted:function(){
                    this.bScrollInit();
                    this.initNoticeList();
                }
            })
        })
    })();
    </script>
{/block}