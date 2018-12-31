<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport"content="width=device-width,initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=no">
    <meta name="browsermode" content="application"/>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <!-- 禁止百度转码 -->
    <meta http-equiv="Cache-Control" content="no-siteapp" />
    <!-- uc强制竖屏 -->
    <meta name="screen-orientation" content="portrait">
    <!-- QQ强制竖屏 -->
    <meta name="x5-orientation" content="portrait">
    <title>今日签到</title>
    <link rel="stylesheet" type="text/css" href="{__WAP_PATH}sx/css/reset.css" />
    <link rel="stylesheet" type="text/css" href="{__WAP_PATH}sx/font/iconfont.css" />
    <link rel="stylesheet" type="text/css" href="{__WAP_PATH}sx/css/style.css" />
    <script type="text/javascript" src="{__WAP_PATH}sx/js/media.js"></script>
    <script type="text/javascript" src="{__WAP_PATH}sx/js/jquery-1.11.2.min.js"></script>
    <script type="text/javascript" src="{__WAP_PATH}sx/js/iscroll.js"></script>
    {include file="public/requirejs"}
</head>
<body class="red-bg">
<div class="user-singin" id="sign">
    <section>
        <header class="flex">
            <div class="user-count-wrapper flex">
                <p>当前积分</p>
                <p class="count">{$userInfo.integral|floatval}</p>
                <p class="day">签到{$signCount}天</p>
            </div>
            <div class="sing-in-btn off" v-cloak="" v-if="signed == true">今日已签到</div>
            <div class="sing-in-btn" v-cloak="" v-if="signed == false" @click="goSign">立即签到</div>
        </header>
        <div class="singin-recording">
            <div class="title-bar"><img src="{__WAP_PATH}sx/images/singin-title-bg.jpg" alt=""></div>
            <div id="scroll" class="list-box">
                <ul>
                    {volist name="signList" id="vo"}
                    <li class="flex">
                        <div class="left-wrapper">
                            <p>每日签到奖励</p>
                            <span>{$vo.add_time|date="Y-m-d",###}</span>
                        </div>
                        <i class="right-wrapper">+{$vo.number}</i>
                    </li>
                    {/volist}
                </ul>
            </div>
        </div>
        <!-- 商品分类模板 -->
        <div class="template-prolist liked">
            <div class="title-like flex">
                <span class="title-line left"></span>
                <span>新品推荐</span>
                <span class="title-line right"></span>
            </div>
            <ul class="flex">
                {volist name="goodsList" id="vo"}
                <li>
                    <a href="{:url('store/detail',['id'=>$vo['id']])}">
                        <div class="picture"><img src="{$vo.image}"></div>
                        <div class="product-info">
                            <p class="title">{$vo.store_name}</p>
                            <?php $price = explode('.',$vo['price']); ?>
                            <p class="count-wrapper flex">
                                <span class="price"><i>￥</i>{$price[0]}.<i>{$price[1]}</i></span>
                                <span class="count">已售{$vo.sales}件</span>
                            </p>
                        </div>
                    </a>
                </li>
                {/volist}
            </ul>
        </div>
    </section>
</div>
{include file="public/right_nav" /}
<script>
    $(document).ready(function(){
        requirejs(['vue','store','helper'],function(Vue,store,$h){
           new Vue({
               el:"#sign",
               data:{
                   signed : <?=$signed ? 'true' : 'false'?>
               },
               methods:{
                   goSign:function () {
                       var that = this;
                       store.baseGet($h.U({
                           c:'auth_api',
                           a:'user_sign'
                       }),function (res) {
                            that.signed = true;
                           $h.pushMsg(res.data.msg,function () {
                               location.reload();
                           })
                       });
                   }
               }
           });
        });
        var myScroll = new IScroll('#scroll' , { click: true ,tap: true,scrollbars: 'custom'});
    })
</script>
</body>
</html>