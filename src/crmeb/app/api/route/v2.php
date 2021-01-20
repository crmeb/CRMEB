<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
use think\facade\Route;

/**
 * v1.1 版本路由
 */
Route::group('v2', function () {
    //无需授权接口
    Route::group(function () {
        //公众号授权登录
        Route::get('wechat/auth', 'v2.wechat.WechatController/auth');
        //小程序授权
        Route::get('wechat/routine_auth', 'v2.wechat.AuthController/auth');
        //小程序静默授权
        Route::get('wechat/silence_auth', 'v2.wechat.AuthController/silenceAuthNoLogin');
        //小程序静默授权登陆
        Route::get('wechat/silence_auth_login', 'v2.wechat.AuthController/silenceAuth');
        //公众号静默授权
        Route::get('wechat/wx_silence_auth', 'v2.wechat.WechatController/silenceAuthNoLogin');
        //公众号静默授权登陆
        Route::get('wechat/wx_silence_auth_login', 'v2.wechat.WechatController/silenceAuth');
        //DIY接口
        Route::get('diy/get_diy/[:name]', 'v2.PublicController/getDiy');
        //是否强制绑定手机号
        Route::get('bind_status', 'v2.PublicController/bindPhoneStatus');
        //小程序授权绑定手机号
        Route::post('auth_bindind_phone', 'v2.wechat.AuthController/authBindingPhone');
        //小程序手机号登录直接绑定
        Route::post('phone_silence_auth', 'v2.wechat.AuthController/silenceAuthBindingPhone');
        //微信手机号登录直接绑定
        Route::post('phone_wx_silence_auth', 'v2.wechat.WechatController/silenceAuthBindingPhone');
    });
    //需要授权
    Route::group(function () {

        Route::post('reset_cart', 'v2.store.StoreCartController/resetCart')->name('resetCart');
        Route::get('new_coupon', 'v2.store.StoreCouponsController/getNewCoupon')->name('getNewCoupon');//获取新人券
        Route::get('get_today_coupon', 'v2.store.StoreCouponsController/getTodayCoupon');//新优惠券弹窗接口
        Route::post('user/user_update', 'v2.wechat.AuthController/updateInfo');
        Route::post('order/product_coupon/:orderId', 'v2.store.StoreCouponsController/getOrderProductCoupon');
        Route::get('cart_list', 'v2.store.StoreCartController/getCartList');
        Route::get('get_attr/:id/:type', 'v2.store.StoreProductController/getProductAttr');
        Route::post('set_cart_num', 'v2.store.StoreCartController/setCartNum');
        Route::get('user/service/record', 'v2.user.StoreService/record')->name('userServiceRecord');//客服聊天记录
        //订单申请发票
        Route::post('order/make_up_invoice', 'v2.order.StoreOrderInvoiceController/makeUp')->name('orderMakeUpInvoice');
        //优惠券
        Route::get('coupons', 'v2.store.StoreCouponsController/lst')->name('couponsList'); //可领取优惠券列表
        //用户发票列表
        Route::get('invoice', 'v2.user.UserInvoiceController/invoiceList')->name('userInvoiceLIst');
        //单个发票详情
        Route::get('invoice/detail/:id', 'v2.user.UserInvoiceController/invoice')->name('userInvoiceDetail');
        //修改|添加发票
        Route::post('invoice/save', 'v2.user.UserInvoiceController/saveInvoice')->name('userInvoiceSave');
        //设置默认发票
        Route::post('invoice/set_default/:id', 'v2.user.UserInvoiceController/setDefaultInvoice')->name('userInvoiceSetDefault');
        //获取默认发票
        Route::get('invoice/get_default/:type', 'v2.user.UserInvoiceController/getDefaultInvoice')->name('userInvoiceGetDefault');
        //删除发票
        Route::get('invoice/del/:id', 'v2.user.UserInvoiceController/delInvoice')->name('userInvoiceDel');
        //订单申请开票记录
        Route::get('order/invoice_list', 'v2.order.StoreOrderInvoiceController/list')->name('orderInvoiceList');
        //订单开票详情
        Route::get('order/invoice_detail/:uni', 'v2.order.StoreOrderInvoiceController/detail')->name('orderInvoiceList');

        //用户搜索记录
        Route::get('user/search_list', 'v2.user.UserSearchController/getUserSeachList')->mame('userSearchList');
        //清除搜索记录
        Route::get('user/clean_search', 'v2.user.UserSearchController/cleanUserSearch')->mame('cleanUserSearch');

    })->middleware(\app\api\middleware\AuthTokenMiddleware::class, true);
    //授权不通过,不会抛出异常继续执行
    Route::group(function () {

        Route::get('subscribe', 'v2.PublicController/subscribe')->name('WechatSubscribe');// 微信公众号用户是否关注
        //公共类
        Route::get('index', 'v2.PublicController/index')->name('index');//首页

    })->middleware(\app\api\middleware\AuthTokenMiddleware::class, false);

})->middleware(\app\http\middleware\AllowOriginMiddleware::class);
