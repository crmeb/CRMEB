{extend name="public/container"}
{block name="head_top"}
<link rel="stylesheet" href="{__PLUG_PATH}swiper/swiper-3.4.1.min.css">
<script type="text/javascript" src="{__PLUG_PATH}swiper/swiper-3.4.1.jquery.min.js"></script>
<link rel="stylesheet" type="text/css" href="{__WAP_PATH}sx/css/reset.css"/>
<link rel="stylesheet" type="text/css" href="{__WAP_PATH}sx/css/swiper-3.4.1.min.css" />
<link rel="stylesheet" type="text/css" href="{__WAP_PATH}sx/font/iconfont.css" />
<link rel="stylesheet" type="text/css" href="{__WAP_PATH}sx/css/style.css" />
<script type="text/javascript" src="{__WAP_PATH}sx/js/media.js"></script>
<script type="text/javascript" src="{__PLUG_PATH}layer/layer.js"></script>
<script type="text/javascript" src="{__PLUG_PATH}jquery.downCount.js"></script>
<script type="text/javascript" src="{__WAP_PATH}sx/js/swiper-3.4.1.jquery.min.js"></script>
<script type="text/javascript" src="{__WAP_PATH}sx/js/iscroll.js"></script>
{/block}
{block name="title"}
{$storeInfo.title}
{/block}
{block name="content"}
<body style="background:#f5f5f5">
    <div class="wrapper product-con" id="store_detail">
        <section>
            <!-- 未关注 -->
            {if condition="!$user['subscribe']"}
            <div class="not-concerned flex">
                <div class="left-wrapper flex">
                    <img class="logo" src="{$site_logo}" alt="">
                    <span>{$site_name}</span>
                </div>
                <a class="go" href="javascript:void(0)" @click="ShowQrcode">立即关注</a>
            </div>
            {/if}
            <div class="banner">
                <ul class="swiper-wrapper">
                    {volist name="storeInfo.images" id="vo"}
                    <li class="swiper-slide"><a href=""><img src="{$vo}" /></a></li>
                    {/volist}
                </ul>
                <div class="swiper-pagination"></div>
                <div class="pro-coundown">
                    <span class="txt"><i class="icon-sd"></i>限时秒杀</span>
                    <div class="countdown" data-time="{$storeInfo.stop_time|date='Y/m/d H:i:s',###}">
                        <span class="days" style="color: #fff !important;">0</span>
                        <i>天</i>
                        <span class="hours" style="color: #fff !important;">00</span>
                        <i>时</i>
                        <span class="minutes" style="color: #fff !important;">00</span>
                        <i>分</i>
                        <span class="seconds" style="color: #fff !important;">00</span>
                        <i>秒</i>
                    </div>
                </div>
            </div>
            <div class="product-info">
                <div class="title">{$storeInfo.title}</div>
                <div class="price flex"><p>￥{$storeInfo.price} <em>￥{$storeInfo.product_price}</em></p><p class="count">已拼{$storeInfo.sales}单<span>{$storeInfo.people}</span>人拼单</p></div>
                <div class="price warn flex"><p class="count"><i></i>拼团倒计时结束时未能拼单者视为抢购失败,将发起退款</p></div>
            </div>
            <div class="pro-spell-list">
                <div class="title">
                    <span><?php $countPink = count($pink);echo $countPink;?>人在拼单，可直接参与</span>
                    {if condition="$countPink"}
                    <a class="menulist fr" @click="menuList" href="javascript:void(0)">查看更多</a>
                    {/if}
                    <div class="tmp-more-list">
                        <div class="more-list-title">正在拼单</div>
                        <div id="scroll" class="more-list-con">
                            <ul>
                                {volist name="pink" id="vo"}
                                <li>
                                    <div class="avatar"><img src="{$vo.avatar}" /></div>
                                    <div class="user-info">
                                        <p class="name">{$vo.nickname}<span>还差{$vo.count}人</span></p>
                                        <div class="count-down">剩余
                                            <div class="count-time-{$vo.id}" data-time="{$vo.stop_time|date='Y/m/d H:i:s',###}">
                                                <span class="hours">00</span>
                                                :
                                                <span class="minutes">00</span>
                                                :
                                                <span class="seconds">00</span>
                                            </div>
                                        </div>
                                    </div>
                                    <a class="join-in" href="{:Url('my/order_pink',['id'=>$vo['id']])}">去拼单</a>
                                </li>
                                {/volist}
                            </ul>
                        </div>
                        <span class="more-list-close" @click="moreListClose"></span>
                    </div>
                </div>
                <ul class="list-box">
                    {volist name="pink" id="vo" offset="0" length="2"}
                        <li class="flex border-1px">
                            <div class="user-info flex">
                                <img class="avatar" src="{$vo.avatar}" />
                                <p>{$vo.nickname}</p>
                            </div>
                            <div class="txt-wrapper flex" v-pre>
                                <div class="count-time">
                                    <p>还差<span class="num">{$vo.count}人</span>拼成</p>
                                    <div class="timer-wrapper">
                                        <div class="count-time-{$vo.id}" data-time="{$vo.stop_time|date='Y/m/d H:i:s',###}">
                                            <span class="hours">00</span>
                                            :
                                            <span class="minutes">00</span>
                                            :
                                            <span class="seconds">00</span>
                                        </div>
                                    </div>
                                </div>
                                <a class="join-in" href="{:Url('my/order_pink',['id'=>$vo['id']])}">去拼单</a>
                            </div>
                        </li>
                    {/volist}
                </ul>
            </div>
            <!-- 评价 -->
            {notempty name="reply"}
            <div class="item-box">
                <div class="item-tit">
                    <i class="line"></i>
                    <i class="iconfont icon-pinglun1"></i>
                    <span>评价</span>
                    <i class="line"></i>
                </div>
                <div class="assess-hot">
                    <p class="title">宝贝评价({$replyCount})</p>
                    <div class="assess-hot-con">
                        <div class="user-info flex">
                            <div class="avatar"><img src="{$reply.avatar}"/></div>
                            <div class="name">{$reply.nickname}</div>
                            <div class="star{$reply.star} all"><span class="num"></span></div>
                        </div>
                        <div class="txt-info">{$reply.comment}</div>
                        <div class="pro-parameter"><span>{$reply.add_time}</span> <span>{$reply.suk}</span></div>
                        {gt name="replyCount" value="1"}
                        <a class="more" href="{:Url('reply_list',['productId'=>$storeInfo['id']])}">查看全部评价</a>
                        {/gt}
                    </div>
                </div>
            </div>
            {/notempty}
            <!-- 详情 -->
            <div class="item-box">
                <div class="item-tit">
                    <i class="line"></i><i class="iconfont icon-icon-tupian"></i><span>详情</span><i class="line"></i>
                </div>
                <div class="con-box">{$storeInfo.description}</div>
            </div>
            <!-- 购买按钮 -->
            <div class="footer-bar flex">
                <a href="{:Url('index/index')}">
                    <span class="index-icon shouye"></span>
                    <p>首页</p>
                </a>
                <a href="{:Url('service/service_list')}">
                    <span class="index-icon kefu"></span>
                    <p>客服</p>
                </a>
                {if condition="$storeInfo['stop_time'] GT time()"}
                <div class="big-btn confirm" @click="goBuy">
                    <p class="confirm-price"><span>￥</span>{$storeInfo.price}</p>
                    <p>立即开团</p>
                </div>
                {else/}
                <div class="big-btn stop">
                    <p class="confirm-price"><span>￥</span>{$storeInfo.price}</p>
                    <p>拼团结束</p>
                </div>
                {/if}
            </div>
        </section>
        <div class="model-bg"></div>
        <div style="height:1rem;"></div>
    </div>
    <script type="text/javascript">
        $(document).ready(function() {
            var myBanner = new Swiper('.banner', {
                paginationClickable: false,
                autoplay:3000,
                loop:true,
                speed:1000,
                autoplayDisableOnInteraction : false,
                pagination : '.swiper-pagination',
            });
            window.$pindAll = <?php echo json_encode($pindAll);?>;
            $.each($pindAll,function (index,item) {
                $('.count-time-'+item).downCount({
                    date: $('.count-time-'+item).attr('data-time'),
                    offset: +8
                });
            })
        }); 
    </script>
    <script type="text/javascript">
        window.$wechat_qrcode = "{$wechat_qrcode}";
        window.$site_name = "{$site_name}";
        window.$product = <?php unset($storeInfo['description']); echo json_encode($storeInfo);?>;
        (function ($) {
            requirejs(['vue', 'axios', 'helper', 'store', 'wap/crmeb/module/store/shop-card'], function (Vue, axios, $h, storeApi, shopCard) {
            new Vue({
                el: "#store_detail",
                components: {'shop-card': shopCard},
                data: {
                    cardShow: false,
                    product: $product,
                    wechatQrcode: $wechat_qrcode,
                    siteName: $site_name,
                    productValue: {},
                    productCardInfo: {},
                    status: {like: false, collect: false},
                    cartNum: 1
                },
                methods: {
                    menuList:function () {
                        if($('.model-bg').css('z-index')==-1){
                            $('.model-bg').css('z-index','999');
                            $('.model-bg').addClass('on');
                            $('.tmp-more-list').addClass('show');
                            var myScroll = new IScroll('#scroll' , { click: true ,tap: true,scrollbars: 'custom'});
                        }
                    },
                    moreListClose:function () {
                        $('.tmp-more-list').removeClass('show');
                        $('.model-bg').removeClass('on').css('z-index','-1');
                    },
                    goBuy: function () {
                        storeApi.goBuy({
                            cartNum: 1,
                            uniqueId: 0,
                            productId: this.product.product_id,
                            combinationId: this.product.id
                        }, function (cartId) {
                            location.href = $h.U({c: 'store', a: 'combination_order', q: 'cartId='+ cartId});
                        });
                    },
                    setProductCardInfo: function (info) {
                        info || (info = {});
                        this.$set(this, 'productCardInfo', {
                            image: info.image !== undefined ? info.image : this.product.image,
                            price: info.price !== undefined ? info.price : this.product.price,
                            stock: info.stock !== undefined ? info.stock : this.product.stock
                        });
                    },
                    pushMsg: function (msg, fn) {
                        $h.pushMsg(msg, fn)
                    },
                    init: function () {
                        new Swiper('.banner', {
                            paginationClickable: false,
                            autoplay: 3000,
                            loop: true,
                            speed: 1000,
                            autoplayDisableOnInteraction: false,
                            pagination: '.swiper-pagination',
                        });
                        $('.detail_ul li').each(function(i) {
                            $(this).click(function() {
                                $('.detail_ul li').removeClass('active');
                                $(this).addClass('active');
                                $('.detail_ul_con').hide();
                                $('.detail_ul_con').eq(i).show();
                            });
                        });
                        $('.detail_ul li').eq(0).trigger('click');
                    },
                    getCartNum: function () {
                        var that = this;
                        storeApi.getCartNum(function (cartNum) {
                            that.cartNum = cartNum;
                        });
                    },
                    ShowQrcode:function () {
                        that = this;
                        layer.open({
                            type: 1,
                            shade: true,
                            shadeClose : true,
                            anim  : 2,
                            area: ['5rem', '5.1rem'],
                            title: false, //不显示标题
                            content: '<img src="'+that.wechatQrcode+'" alt="'+that.siteName+'" title="'+that.siteName+'" style="width: 5rem;"/>', //捕获的元素，注意：最好该指定的元素要存放在body最外层，否则可能被其它的相对元素所影响
                        });
                    }
                },
                mounted: function () {
                    var wxApi = mapleWx($jssdk(), function () {
                        this.onMenuShareAll({
                            title: $product.store_name,
                            desc: $product.store_info || $product.store_name,
                            imgUrl: location.origin + $product.image,
                            link: location.href
                        });
                    });
                    $.each($pindAll,function (index,item) {
                        $('.count-time-'+item).downCount({
                            date: $('.count-time-'+item).attr('data-time'),
                            offset: +8
                        });
                    })
                    var countTime = $('.countdown').attr('data-time');
                    $('.countdown').downCount({
                        date: countTime,
                        offset: +8
                    });
                    this.getCartNum();
                    this.init();
                    this.setProductCardInfo();
                }
            });
        });
        })($);
    </script>
</body>
{/block}