<template>
    <div class="scroll-box">
        <div class="title-box">商品详情</div>
        <swiper :options="swiperOption" class="swiper-box">
            <swiper-slide class="swiper-slide" v-for="(item,index) in goodsInfo.slider_image" :key="index">
                <img :src="item"/>
            </swiper-slide>
            <!-- 分页器 -->
        </swiper>
        <div class="goods_info">
            <div class="number-wrapper">
                <div class="price"><span>¥</span>{{goodsInfo.price}}</div>
                <div class="old-price">¥{{goodsInfo.vip_price}}<img src="@/assets/images/goods_vip.png" alt="" width="28"></div>
            </div>
            <div class="name">{{goodsInfo.store_name}}</div>
            <div class="msg">
                <div class="item">原价:￥{{goodsInfo.ot_price}}</div>
                <div class="item">销量:{{goodsInfo.sales}}</div>
                <div class="item">库存:{{goodsInfo.stock}}</div>
            </div>
        </div>
        <div class="con-box">
            <div class="title-box">商品介绍</div>
            <div class="content" v-html="goodsInfo.description"></div>
        </div>
    </div>
</template>

<script>
    import { productInfo } from '@/api/kefu'
    export default {
        name: "goods_detail",
        props:{
            goodsId:{
                type:String | Number,
                default:''
            }
        },
        data(){
            return {
                value2: 0,
                goodsInfo:{},
                goodsId:'',
                swiperOption:{
                    //显示分页
                    pagination: {
                        el: '.swiper-pagination'
                    },
                    //设置点击箭头
                    navigation: {
                        nextEl: '.swiper-button-next',
                        prevEl: '.swiper-button-prev'
                    },
                    //自动轮播
                    autoplay: {
                        delay: 2000,
                        //当用户滑动图片后继续自动轮播
                        disableOnInteraction: false,
                    },
                    //开启循环模式
                    loop: true
                }
            }
        },
        created() {
            this.goodsId = this.$route.query.goodsId
        },
        mounted() {
            this.getInfo()
        },
        methods:{
            getInfo(){
                productInfo(this.goodsId).then(res=>{
                    this.goodsInfo = res.data
                })
            }
        }
    }
</script>

<style lang="stylus" scoped>
    .scroll-box{
        height 100%
        overflow-y scroll
        -webkit-overflow-scrolling touch
    }
    .title-box{
        height:.8rem
        line-height .8rem
        background #fff
        text-align center
        color #333
        font-size 16px
    }
    .swiper-box
        width 100%
        height auto
        img
            width 100%
            height 100%
            display block
    .goods_info
        padding 15px
        background #fff
        .number-wrapper
            display flex
            align-items center
            .price
                color #FF3838
                font-size .3rem
                span
                    font-size .26rem
            .old-price
                font-size .3rem
                margin-left .2rem
                color #333333
        .name
            font-size .32rem
            color #333
        .msg
            display flex
            align-items center
            justify-content space-between
            margin-top .2rem
            .item
                color #999999
                font-size .28rem
    .con-box
        margin-top 10px
        padding-bottom 20px
        img
            display block
</style>
