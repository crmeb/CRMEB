<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
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
        Route::group(function () {
            //小程序登录页面自动加载，返回用户信息的缓存key，返回是否强制绑定手机号
            Route::get('routine/auth_type', 'v2.wechat.AuthController/authType')->option(['real_name' => '小程序页面登录类型']);
            //小程序授权登录，返回token
            Route::get('routine/auth_login', 'v2.wechat.AuthController/authLogin')->option(['real_name' => '小程序授权登录']);
            //小程序授权绑定手机号
            Route::post('routine/auth_binding_phone', 'v2.wechat.AuthController/authBindingPhone')->option(['real_name' => '小程序授权绑定手机号']);
            //小程序手机号直接登录
            Route::post('routine/phone_login', 'v2.wechat.AuthController/phoneLogin')->option(['real_name' => '手机号直接登录']);
            //小程序授权后绑定手机号
            Route::post('routine/binding_phone', 'v2.wechat.AuthController/BindingPhone')->option(['real_name' => '小程序授权后绑定手机号']);

            //公众号授权登录，返回token
            Route::get('wechat/auth_login', 'v2.wechat.WechatController/authLogin')->option(['real_name' => '公众号授权登录']);
            //公众号授权绑定手机号
            Route::post('wechat/auth_binding_phone', 'v2.wechat.WechatController/authBindingPhone')->option(['real_name' => '小程序授权绑定手机号']);

        })->option(['mark' => 'wechat_auto', 'mark_name' => '微信授权']);

        Route::group(function () {
            //获取门店自提开启状态
            Route::get('diy/get_store_status', 'v2.PublicController/getStoreStatus')->option(['real_name' => '获取门店自提开启状态']);
            //一键换色
            Route::get('diy/color_change/:name', 'v2.PublicController/colorChange')->option(['real_name' => '一键换色']);
            //DIY接口
            Route::get('diy/get_diy/[:name]', 'v2.PublicController/getDiy')->option(['real_name' => '获取DIY数据']);
            Route::get('diy/get_version/[:name]', 'v2.PublicController/getVersion')->option(['real_name' => '获取DIY版本号']);
        })->option(['mark' => 'diy', 'mark_name' => 'DIY']);
    });
    //需要授权
    Route::group(function () {

        Route::post('reset_cart', 'v2.store.StoreCartController/resetCart')->name('resetCart')->option(['real_name' => '清除购物车', 'mark' => 'cart', 'mark_name' => '购物车']);
        Route::get('new_coupon', 'v2.store.StoreCouponsController/getNewCoupon')->name('getNewCoupon')->option(['real_name' => '获取新人券', 'mark' => 'coupons', 'mark_name' => '优惠券']);//获取新人券
        Route::post('order/product_coupon/:orderId', 'v2.store.StoreCouponsController/getOrderProductCoupon')->option(['real_name' => '获取订单下管理的优惠券', 'mark' => 'coupons', 'mark_name' => '优惠券']);
        Route::get('user/service/record', 'v2.user.StoreService/record')->name('userServiceRecord')->option(['real_name' => '客服聊天记录', 'parent' => 'user', 'cate_name' => '客服']);//客服聊天记录
        Route::get('cart_list', 'v2.store.StoreCartController/getCartList')->option(['real_name' => '获取购物车列表', 'mark' => 'cart', 'mark_name' => '购物车']);
        Route::get('get_attr/:id/:type', 'v2.store.StoreProductController/getProductAttr')->option(['real_name' => '获取商品规格', 'mark' => 'cart', 'mark_name' => '购物车']);
        Route::post('set_cart_num', 'v2.store.StoreCartController/setCartNum')->option(['real_name' => '获取购物车数量', 'mark' => 'cart', 'mark_name' => '购物车']);

        Route::group(function () {
            //订单申请发票
            Route::post('order/make_up_invoice', 'v2.order.StoreOrderInvoiceController/makeUp')->name('orderMakeUpInvoice')->option(['real_name' => '订单申请发票']);
            //用户发票列表
            Route::get('invoice', 'v2.user.UserInvoiceController/invoiceList')->name('userInvoiceLIst')->option(['real_name' => '用户发票列表']);
            //单个发票详情
            Route::get('invoice/detail/:id', 'v2.user.UserInvoiceController/invoice')->name('userInvoiceDetail')->option(['real_name' => '单个发票详情']);
            //修改|添加发票
            Route::post('invoice/save', 'v2.user.UserInvoiceController/saveInvoice')->name('userInvoiceSave')->option(['real_name' => '修改|添加发票']);
            //设置默认发票
            Route::post('invoice/set_default/:id', 'v2.user.UserInvoiceController/setDefaultInvoice')->name('userInvoiceSetDefault')->option(['real_name' => '设置默认发票']);
            //获取默认发票
            Route::get('invoice/get_default/:type', 'v2.user.UserInvoiceController/getDefaultInvoice')->name('userInvoiceGetDefault')->option(['real_name' => '获取默认发票']);
            //删除发票
            Route::get('invoice/del/:id', 'v2.user.UserInvoiceController/delInvoice')->name('userInvoiceDel')->option(['real_name' => '删除发票']);
            //订单申请开票记录
            Route::get('order/invoice_list', 'v2.order.StoreOrderInvoiceController/list')->name('orderInvoiceList')->option(['real_name' => '订单申请开票记录']);
            //订单开票详情
            Route::get('order/invoice_detail/:uni', 'v2.order.StoreOrderInvoiceController/detail')->name('orderInvoiceList')->option(['real_name' => '订单开票详情']);
        })->option(['mark' => 'invoice', 'mark_name' => '发票']);

        //清除搜索记录
        Route::get('user/clean_search', 'v2.user.UserSearchController/cleanUserSearch')->mame('cleanUserSearch')->option(['real_name' => '清除搜索记录']);

        //抽奖活动详情
        Route::get('lottery/info/:factor', 'v2.activity.LuckLotteryController/lotteryInfo')->mame('lotteryInfo')->option(['real_name' => '抽奖活动详情']);
        //参与抽奖
        Route::post('lottery', 'v2.activity.LuckLotteryController/luckLottery')->mame('luckLottery')->middleware(BlockerMiddleware::class)->option(['real_name' => '参与抽奖']);
        //领取奖品
        Route::post('lottery/receive', 'v2.activity.LuckLotteryController/lotteryReceive')->mame('lotteryReceive')->middleware(BlockerMiddleware::class)->option(['real_name' => '领取奖品']);
        //抽奖记录
        Route::get('lottery/record', 'v2.activity.LuckLotteryController/lotteryRecord')->mame('lotteryRecord')->option(['real_name' => '抽奖记录']);

        //获取分销等级列表
        Route::get('agent/level_list', 'v2.agent.AgentLevel/levelList')->name('agentLevelList')->option(['real_name' => '获取分销等级列表']);
        //获取分销等级任务列表
        Route::get('agent/level_task_list', 'v2.agent.AgentLevel/levelTaskList')->name('agentLevelTaskList')->option(['real_name' => '获取分销等级任务列表']);

    })->middleware(\app\api\middleware\AuthTokenMiddleware::class, true);

    //授权不通过,不会抛出异常继续执行
    Route::group(function () {
        //用户搜索记录
        Route::get('user/search_list', 'v2.user.UserSearchController/getUserSeachList')->mame('userSearchList')->option(['real_name' => '用户搜索记录']);
        Route::get('get_today_coupon', 'v2.store.StoreCouponsController/getTodayCoupon')->option(['real_name' => '新优惠券弹窗接口']);//新优惠券弹窗接口
        Route::get('subscribe', 'v2.PublicController/subscribe')->name('WechatSubscribe')->option(['real_name' => '微信公众号用户是否关注']);// 微信公众号用户是否关注
        //公共类
        Route::get('index', 'v2.PublicController/index')->name('index')->option(['real_name' => '首页']);//首页
        //优惠券
        Route::get('coupons', 'v2.store.StoreCouponsController/lst')->name('couponsList')->option(['real_name' => '可领取优惠券列表']); //可领取优惠券列表
    })->middleware(\app\api\middleware\AuthTokenMiddleware::class, false)
        ->option(['mark' => 'common', 'mark_name' => '公共接口']);

})->middleware(\app\http\middleware\AllowOriginMiddleware::class)->middleware(\app\api\middleware\StationOpenMiddleware::class);
