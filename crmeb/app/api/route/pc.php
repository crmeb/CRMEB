<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
use think\facade\Route;
use think\facade\Config;
use think\Response;

Route::group('pc', function () {
    //登陆接口
    Route::group(function () {
        Route::get('key', 'pc.LoginController/getLoginKey')->name('getLoginKey');//获取扫码登录key
        Route::get('scan/:key', 'pc.LoginController/scanLogin')->name('scanLogin');//检测扫码情况
        Route::get('get_appid', 'pc.LoginController/getAppid')->name('getAppid');//检测扫码情况
        Route::get('wechat_auth', 'pc.LoginController/wechatAuth')->name('wechatAuth');//检测扫码情况
    })->middleware(\app\http\middleware\AllowOriginMiddleware::class)->middleware(\app\api\middleware\StationOpenMiddleware::class);

    //未授权接口
    Route::group(function () {
        Route::get('get_pay_vip_code', 'pc.HomeController/getPayVipCode')->name('getPayVipCode');//获取付费会员购买页面二维码
        Route::get('get_product_phone_buy', 'pc.HomeController/getProductPhoneBuy')->name('getProductPhoneBuy');//手机购买跳转url配置
        Route::get('get_banner', 'pc.HomeController/getBanner')->name('getBanner');//PC首页轮播图
        Route::get('get_category_product', 'pc.HomeController/getCategoryProduct')->name('getCategoryProduct');//首页分类尚品
        Route::get('get_products', 'pc.ProductController/getProductList')->name('getProductList');//商品列表
        Route::get('get_product_code/:product_id/[:type]', 'pc.ProductController/getProductRoutineCode')->name('getProductRoutineCode');//商品详情小程序二维码
        Route::get('get_city/:pid', 'pc.PublicController/getCity')->name('getCity');//获取城市数据
        Route::get('check_order_status/:order_id/:end_time', 'pc.OrderController/checkOrderStatus')->name('checkOrderStatus');//轮询订单状态接口
        Route::get('get_company_info', 'pc.PublicController/getCompanyInfo')->name('getCompanyInfo');//获取公司信息
        Route::get('get_recommend/:type', 'pc.ProductController/getRecommendList')->name('getRecommendList');//获取推荐商品
        Route::get('get_wechat_qrcode', 'pc.PublicController/getWechatQrcode')->name('getWechatQrcode');//获取关注二维码
        Route::get('get_good_product', 'pc.ProductController/getGoodProduct')->name('getGoodProduct');//获取优品推荐
    })->middleware(\app\http\middleware\AllowOriginMiddleware::class)->middleware(\app\api\middleware\StationOpenMiddleware::class)->middleware(\app\api\middleware\AuthTokenMiddleware::class, false);

    //会员授权接口
    Route::group(function () {
        Route::get('get_cart_list', 'pc.CartController/getCartList')->name('getCartList');//购物车列表
        Route::get('get_balance_record/:type', 'pc.UserController/getBalanceRecord')->name('getBalanceRecord');//余额记录
        Route::get('get_order_list', 'pc.OrderController/getOrderList')->name('getOrderList');//订单列表
        Route::get('get_refund_order_list', 'pc.OrderController/getRefundOrderList')->name('getRefundOrderList');//退款订单列表
        Route::get('get_collect_list', 'pc.UserController/getCollectList')->name('getCollectList');//收藏列表
    })->middleware(\app\http\middleware\AllowOriginMiddleware::class)->middleware(\app\api\middleware\StationOpenMiddleware::class)->middleware(\app\api\middleware\AuthTokenMiddleware::class, true);

    Route::miss(function () {
        if (app()->request->isOptions()) {
            $header = Config::get('cookie.header');
            unset($header['Access-Control-Allow-Credentials']);
            return Response::create('ok')->code(200)->header($header);
        } else
            return Response::create()->code(404);
    });
})->middleware(\app\http\middleware\AllowOriginMiddleware::class)->middleware(\app\api\middleware\StationOpenMiddleware::class);
