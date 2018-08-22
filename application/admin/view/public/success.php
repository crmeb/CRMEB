<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
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
        <link rel="stylesheet" type="text/css" href="{__MODULE_PATH}error/css/reset-2.0.css" />
        <link rel="stylesheet" type="text/css" href="{__MODULE_PATH}error/css/style.css" />
        <script src="{__FRAME_PATH}js/jquery.min.js"></script>
    </head>
    <style type="text/css">

    </style>
    <body>
        <div class="link-wrapper">
             <div class="success">
                <img src="{__MODULE_PATH}success/images/success-icon.png" />
                <div class="text">
                    <p class="status">{$msg}！</p>
                    <p class="countdown"><span class="nmb">3</span>秒钟后自动跳转，<a class="back" href="javascript:void(0);">点此手动跳转</a></p>
                    <p class="tips">cmseb提示您：您访问的页面正在跳转中，请耐心等待…</p>
                </div>
            </div>
        </div>
        <script>
            $(document).ready(function(){
                var url = "{$url}";
                if(!url) url = document.referrer;
                var nmb = $('.nmb').html();
                var iCount = setInterval(function (e) {
                    $('.nmb').html(--nmb);
                    if(nmb < 1){
                        clearInterval(iCount);
                        if(url) location.replace(url);
                    }
                }, 1000);
                $('.back').on('click',function(){
                    if(url) location.replace(url);
                });
            });
        </script>
    </body>
</html>