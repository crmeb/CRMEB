{extend name="public/container"}
{block name="title"}评价页面{/block}
{block name="content"}
<body>
<div id="order-reply" class="user-assess">
    <section>
        <div class="pro-info flex">
            <div class="picture"><img src="{$cartInfo.cart_info.productInfo.image}" /></div>
            <div class="product-box flex">
                <p class="title">{$cartInfo.cart_info.productInfo.store_name}</p>
                <p class="description"><?php echo isset($cartInfo['cart_info']['productInfo']['attrInfo']) ? $cartInfo['cart_info']['productInfo']['attrInfo']['suk'] : ''; ?></p>
                <p class="count"><span class="price">￥{$cartInfo.cart_info.truePrice}</span>X{$cartInfo.cart_info.cart_num}</p>
            </div>
        </div>
        <div class="message-box">
            <div class="text-wrapper">
                <textarea v-model="comment" placeholder="宝贝满足你的期待么？说说你的想法，分享给想买的他们吧~"></textarea>
            </div>
            <div class="upload-img flex">
                <div class="picture" v-for="pic in pics" v-cloak=""><img :src="pic" /></div>
                <div class="picture add-img flex" @click="upload" v-show = 'pics.length <= 6'>
                    <img src="{__WAP_PATH}crmeb/images/camera-icon.png" />
                    <p>添加图片</p>
                </div>
            </div>
        </div>
        <div class="user-score">
            <div class="score-item flex">
                <span class="txt">产品评分</span>
                <div class="star-wrapper flex" data-type="product_score">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <span class="count" v-show="product_score > 0" v-cloak=""><i v-text="product_score">1</i>分</span>
            </div>
            <div class="score-item flex">
                <span class="txt">商家服务</span>
                <div class="star-wrapper flex" data-type="service_score">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <span class="count" v-show="service_score > 0" v-cloak=""><i v-text="service_score"></i>分</span>
            </div>
            <a href="javascript:void(0);" class="address-add" @click="submit" style=" z-index: 99;position: fixed;left: 0;bottom: 0;width: 100%;height: .7rem;line-height: .7rem;text-align: center;background-color: #ff8d10;text-align: center;color: #fff;">立即评价</a>
        </div>
    </section>
</div>
<script type="text/javascript">
    requirejs(['vue','store','helper'],function(Vue,storeApi,$h){
        new Vue({
            el:"#order-reply",
            data:{
                unique:"<?=$cartInfo['unique']?>",
                comment:'',
                pics:[],
                product_score:0,
                service_score:0
            },
            methods:{
                upload:function(){
                    var that = this;
                    mapleWx($jssdk(),function(){
                       storeApi.wechatUploadImg(this,6 - that.pics.length,function(res){
                           that.pics = that.pics.concat(res);
                       });
                    });
                },
                submit:function(){
                    if(this.product_score < 1) return $h.pushMsgOnce('请为产品评分');
                    if(this.service_score < 1) return $h.pushMsgOnce('请为商家服务评分');
                    $h.loadFFF();
                    storeApi.userCommentProduct(this.unique,this.$data,function(res){
                        $h.loadClear();
                        $h.pushMsgOnce('评价成功',function(){
                            location.replace(document.referrer);
                        });
                    },function(){ $h.loadClear();return true;});
                }
            },
            mounted:function(){
                var that = this;
                $('.star-wrapper span').on('click', function(){
                    var p = $(this).parent(),type = p.data('type'),l = $(this).prevAll().length;
                    p.find('span').removeClass('active');
                    $(this).addClass('active').prevAll().addClass('active');
                    that[type] = ++l;
                })
            }
        })
    });
</script>
{/block}