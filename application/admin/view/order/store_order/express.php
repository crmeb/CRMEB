
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=no">
    <meta name="browsermode" content="application"/>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <!-- 禁止百度转码 -->
    <meta http-equiv="Cache-Control" content="no-siteapp">
    <!-- uc强制竖屏 -->
    <meta name="screen-orientation" content="portrait">
    <!-- QQ强制竖屏 -->
    <meta name="x5-orientation" content="portrait">
    <title>物流信息</title>
    <link rel="stylesheet" type="text/css" href="/public/static/css/reset.css"/>
    <link rel="stylesheet" type="text/css" href="/public/wap/crmeb/font/iconfont.css"/>
    <link rel="stylesheet" type="text/css" href="/public/wap/crmeb/css/style.css?2"/>
    <script type="text/javascript" src="/public/static/js/media.js"></script>
    <script type="text/javascript" src="/public/static/plug/jquery-1.10.2.min.js"></script>
    <script type="text/javascript" src="/public/wap/crmeb/js/common.js"></script>
</head>
<body>

<div class="user-order-logistics" style="overflow: hidden;">
    <section>
        <div class="product-info flex">
            <div class="picture"><img src="/public/wap/crmeb/images/express_icon.jpg"/></div>
            <div class="logistics-tip flex">
                <div class="company">物流公司：{$order.delivery_name}</div>
                <div class="number">物流单号：{$order.delivery_id}</div>
            </div>
        </div>
        <div class="logistics-info" style="background-color: inherit;">
            <?php if(!$express || $express['status'] != 0){ ?>
                <div class="empty">
                    <img src="/public/wap/crmeb/images/empty_address.png">
                    <p>暂无查询记录</p>
                </div>
            <?php }else{ ?>
                <ul class="flex">
                    {volist name="express.result.list" id="vo"}
                    <li class="clearfix">
                        <div class="right-wrapper fl">
                            <p>{$vo.status}</p>
                            <span>{$vo.time}</span>
                        </div>
                    </li>
                    {/volist}
                </ul>
            <?php } ?>
        </div>
    </section>
</div>
</body>
</html>
