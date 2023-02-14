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
use app\api\middleware\BlockerMiddleware;
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
        Route::get('diy/get_version/[:name]', 'v2.PublicController/getVersion');
        //是否强制绑定手机号
        Route::get('bind_status', 'v2.PublicController/bindPhoneStatus');
        //小程序授权绑定手机号
        Route::post('auth_bindind_phone', 'v2.wechat.AuthController/authBindingPhone');
        //小程序手机号登录直接绑定
        Route::post('phone_silence_auth', 'v2.wechat.AuthController/silenceAuthBindingPhone');
        //微信手机号登录直接绑定
        Route::post('phone_wx_silence_auth', 'v2.wechat.WechatController/silenceAuthBindingPhone');
        //获取门店自提开启状态
        Route::get('diy/get_store_status', 'v2.PublicController/getStoreStatus');
        //一键换色
        Route::get('diy/color_change/:name', 'v2.PublicController/colorChange');
    });
    //需要授权
    Route::group(function () {
        Route::post('reset_cart', 'v2.store.StoreCartController/resetCart')->name('resetCart');
        Route::get('new_coupon', 'v2.store.StoreCouponsController/getNewCoupon')->name('getNewCoupon');//获取新人券
//        Route::get('get_today_coupon', 'v2.store.StoreCouponsController/getTodayCoupon');//新优惠券弹窗接口
        Route::post('user/user_update', 'v2.wechat.AuthController/updateInfo');
        Route::post('order/product_coupon/:orderId', 'v2.store.StoreCouponsController/getOrderProductCoupon');
        Route::get('user/service/record', 'v2.user.StoreService/record')->name('userServiceRecord');//客服聊天记录
        Route::get('cart_list', 'v2.store.StoreCartController/getCartList');
        Route::get('get_attr/:id/:type', 'v2.store.StoreProductController/getProductAttr');
        Route::post('set_cart_num', 'v2.store.StoreCartController/setCartNum');
        //订单申请发票
        Route::post('order/make_up_invoice', 'v2.order.StoreOrderInvoiceController/makeUp')->name('orderMakeUpInvoice');
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

        //清除搜索记录
        Route::get('user/clean_search', 'v2.user.UserSearchController/cleanUserSearch')->mame('cleanUserSearch');

        //抽奖活动详情
        Route::get('lottery/info/:factor', 'v2.activity.LuckLotteryController/lotteryInfo')->mame('lotteryInfo');
        //参与抽奖
        Route::post('lottery', 'v2.activity.LuckLotteryController/luckLottery')->mame('luckLottery')->middleware(BlockerMiddleware::class);
        //领取奖品
        Route::post('lottery/receive', 'v2.activity.LuckLotteryController/lotteryReceive')->mame('lotteryReceive')->middleware(BlockerMiddleware::class);
        //抽奖记录
        Route::get('lottery/record', 'v2.activity.LuckLotteryController/lotteryRecord')->mame('lotteryRecord');

        //获取分销等级列表
        Route::get('agent/level_list', 'v2.agent.AgentLevel/levelList')->name('agentLevelList');
        //获取分销等级任务列表
        Route::get('agent/level_task_list', 'v2.agent.AgentLevel/levelTaskList')->name('agentLevelTaskList');

    })->middleware(\app\api\middleware\AuthTokenMiddleware::class, true);

    //授权不通过,不会抛出异常继续执行
    Route::group(function () {
        //用户搜索记录
        Route::get('user/search_list', 'v2.user.UserSearchController/getUserSeachList')->mame('userSearchList');
        Route::get('get_today_coupon', 'v2.store.StoreCouponsController/getTodayCoupon');//新优惠券弹窗接口
        Route::get('subscribe', 'v2.PublicController/subscribe')->name('WechatSubscribe');// 微信公众号用户是否关注
        //公共类
        Route::get('index', 'v2.PublicController/index')->name('index');//首页
        //优惠券
        Route::get('coupons', 'v2.store.StoreCouponsController/lst')->name('couponsList'); //可领取优惠券列表
    })->middleware(\app\api\middleware\AuthTokenMiddleware::class, false);

})->middleware(\app\http\middleware\AllowOriginMiddleware::class)->middleware(\app\api\middleware\StationOpenMiddleware::class);
