{extend name="public/container"}
{block name="title"}秒杀产品列表{/block}
{block name="head_top"}
<link rel="stylesheet" href="{__PLUG_PATH}swiper/swiper-3.4.1.min.css">
<script type="text/javascript" src="{__PLUG_PATH}swiper/swiper-3.4.1.jquery.min.js"></script>

{/block
{block name="content"}
<div class="page-index" id="app-index">
    <section>
        <div class="main">
            <!-- 限时秒杀 -->
            {notempty name="seckillProduct"}
            <div class="spike-time">
                <div class="page-tit">
                    <p style="color: #ec0707;">商品限时秒杀</p>
                </div>
                <ul class="product-list">
                    {volist name="seckillProduct" id="vo"}
                    <li>
                        <a class="flex" href="{:Url('store/seckill_detail',['id'=>$vo['id']])}">
                            <div class="product"><img src="{$vo.image}" /></div>
                            <div class="info-box">
                                <p class="tit">{$vo.title}</p>
                                <p class="count-wrappr"><span>限量{$vo.stock}件</span>  <span class="old-price">市场价：{$vo.ot_price}</span></p>
                                <div class="time-wrapper">
                                    <div class="countdown" data-time="{$vo.stop_time|date='Y/m/d H:i:s',###}">
                                        <span class="hours" style="background-color: #ec0707; width:.40rem;">00</span>
                                        <i>:</i>
                                        <span class="minutes" style="background-color: #ec0707; width:.40rem;">00</span>
                                        <i>:</i>
                                        <span class="seconds" style="background-color: #ec0707; width:.40rem;">00</span>
                                    </div>
                                    <p class="new-price">￥{$vo.price}</p>
                                </div>
                            </div>
                        </a>
                    </li>
                    {/volist}
                </ul>
            </div>
            {/notempty}
        </div>
    </section>
    {include file="public/store_menu"}
</div>
<script type="text/javascript" src="{__WAP_PATH}crmeb/js/jquery.downCount.js"></script>
<script type="text/javascript">

    $(document).ready(function() {
        $('.countdown').each(function() {
            var _this = $(this);
            var count =  _this.attr('data-time');
            _this.downCount({
                date: count,
                offset: +8
            }, function () {
                _this.find('span').html('00');
            });
        });
    });
</script>
{/block}