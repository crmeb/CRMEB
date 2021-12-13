<template>
    <div class="goods_detail">
        <div class="goods_detail_wrapper" style="height: 640px;">
            <HappyScroll size="5" resize hide-horizontal>
                <div style="width: 375px">
                    <div class="title-box">商品详情</div>
                    <div class="swiper-box">
                        <Carousel autoplay v-model="value2" loop arrow="never">
                            <CarouselItem v-for="(item,index) in goodsInfo.productInfo.slider_image" :key="index">
                                <div class="demo-carousel"><img :src="item" alt=""></div>
                            </CarouselItem>
                        </Carousel>
                    </div>
                    <div class="goods_info">
                        <div class="number-wrapper">
                            <div class="price"><span>¥</span>{{goodsInfo.productInfo.price}}</div>
                            <div class="old-price">¥{{goodsInfo.productInfo.vip_price}}</div>
                            <div><img src="../../../../assets/images/goods_vip.png"></div>
                        </div>
                        <div class="name">{{goodsInfo.productInfo.store_name}}</div>
                        <div class="msg">
                            <div class="item">原价:￥{{goodsInfo.productInfo.ot_price}}</div>
                            <div class="item">销量:{{goodsInfo.productInfo.sales}}</div>
                            <div class="item">库存:{{goodsInfo.productInfo.stock}}</div>
                        </div>
                    </div>
                    <div class="con-box">
                        <div class="title-box">商品介绍</div>
                        <div class="content" v-html="goodsInfo.productInfo.description"></div>
                    </div>
                </div>
            </HappyScroll>

        </div>
    </div>
</template>

<script>
    import { HappyScroll } from 'vue-happy-scroll'
    import { productInfoApi } from '@/api/product'
    export default {
        name: "goods_detail",
        props:{
            goodsId:{
                type:String | Number,
                default:''
            }
        },
        components:{
            HappyScroll
        },
        data(){
            return {
                value2: 0,
                goodsInfo:{}
            }
        },
        mounted() {
            this.getInfo()
        },
        methods:{
            getInfo(){
                productInfoApi(this.goodsId).then(res=>{
                    this.goodsInfo = res.data
                })
            }
        }
    }
</script>

<style lang="stylus" scoped>
.goods_detail
    .goods_detail_wrapper{
        z-index 20
        position fixed
        left 50%
        top 50%
        transform translate(-50%,-50%)
        width 375px
        background #F0F2F5
    }
    .title-box{
        height:46px
        line-height 46px
        background #fff
        text-align center
        color #333
        font-size 16px
    }
    .swiper-box
        height 375px
        .demo-carousel
            width 375px
            height 375px
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
                font-size 25px
                span
                    font-size 15px
            .old-price
                font-size 15px
                margin-left 10px
                color #333333
        .name
            font-size 16px
            color #333
        .msg
            display flex
            align-items center
            justify-content space-between
            margin-top 10px
            .item
                color #999999
                font-size 14px
    .con-box
        margin-top 10px
        padding-bottom 20px
        background: #f0f2f5;

</style>
