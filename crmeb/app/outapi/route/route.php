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
use app\http\middleware\AllowOriginMiddleware;
use app\outapi\middleware\AuthTokenMiddleware;
use think\facade\Config;
use think\facade\Route;
use think\Response;

Route::group(function () {

    //获取token
    Route::post('access_token', 'Login/getToken')->name('getToken');
    //刷新token
    Route::post('refresh_token', 'Login/refreshToken')->name('refreshToken');

    Route::group(function () {
        //商品分类
        Route::get('category/list', 'StoreCategory/index')->option(['real_name' => '分类列表']);
        Route::get('category/:id', 'StoreCategory/read')->option(['real_name' => '获取分类']);
        Route::post('category', 'StoreCategory/save')->option(['real_name' => '新增分类']);
        Route::put('category/:id', 'StoreCategory/update')->option(['real_name' => '修改分类']);
        Route::delete('category/:id', 'StoreCategory/delete')->option(['real_name' => '删除分类']);
        Route::put('category/set_show/:id/:is_show', 'StoreCategory/set_show')->option(['real_name' => '修改分类状态']);

        //商品
        Route::get('product/list', 'StoreProduct/index')->option(['real_name' => '商品列表']);
        Route::post('product', 'StoreProduct/save')->option(['real_name' => '新增商品']);
        Route::put('product/:id', 'StoreProduct/update')->option(['real_name' => '修改商品']);
        Route::get('product/:id', 'StoreProduct/read')->option(['real_name' => '获取商品']);
        Route::put('product/set_show/:id/:is_show', 'StoreProduct/set_show')->option(['real_name' => '修改商品状态']);
        Route::put('product/stock/upload', 'StoreProduct/uploadStock')->option(['real_name' => '同步商品库存']);

        //订单
        Route::get('order/list', 'StoreOrder/lst')->name('StoreOrderList')->option(['real_name' => '订单列表']);
        Route::get('order/:order_id', 'StoreOrder/read')->name('StoreOrderInfo')->option(['real_name' => '订单详情']);
        Route::put('order/remark/:order_id', 'StoreOrder/remark')->name('StoreOrderRemark')->option(['real_name' => '修改备注信息']);
        Route::put('order/receive/:order_id', 'StoreOrder/receive')->name('StoreOrderReceive')->option(['real_name' => '确认收货']);
        Route::get('order/express_list', 'StoreOrder/express')->name('StoreOrderExpress')->option(['real_name' => '获取物流公司']);
        Route::put('order/delivery/:order_id', 'StoreOrder/delivery')->name('StoreOrderDelivery')->option(['real_name' => '订单发货']);
        Route::put('order/distribution/:order_id', 'StoreOrder/updateDistribution')->name('StoreOrderDistribution')->option(['real_name' => '修改配送信息']);
        Route::get('order/split_cart_info/:order_id', 'StoreOrder/splitCartInfo')->name('StoreOrderSplitCartInfo')->option(['real_name' => '获取订单可拆分商品列表']);
        Route::put('order/split_delivery/:order_id', 'StoreOrder/splitDelivery')->name('StoreOrderSplitDelivery')->option(['real_name' => '拆单发送货']);
        Route::put('order/invoice/:order_id', 'StoreOrder/setInvoice')->option(['real_name' => '修改订单发票']);
        Route::put('order/invoice_status/:order_id', 'StoreOrder/setInvoiceStatus')->option(['real_name' => '修改订单发票状态']);

        //售后订单
        Route::get('refund/list', 'RefundOrder/lst')->option(['real_name' => '售后订单列表']);
        Route::put('refund/remark/:order_id', 'RefundOrder/remark')->option(['real_name' => '售后订单备注']);
        Route::put('refund/:order_id', 'RefundOrder/refundPrice')->option(['real_name' => '售后订单退款']);
        Route::put('refund/agree/:order_id', 'RefundOrder/agree')->option(['real_name' => '商家同意退款']);
        Route::put('refund/refuse/:order_id', 'RefundOrder/refuse')->option(['real_name' => '商家拒绝退款']);
        Route::get('refund/:order_id', 'RefundOrder/read')->option(['real_name' => '售后订单详情']);

        //优惠券
        Route::get('coupon/list', 'StoreCoupon/lst')->option(['real_name' => '优惠券列表']);
        Route::post('coupon', 'StoreCoupon/save')->option(['real_name' => '新增优惠券']);
        Route::put('coupon/status/:id/:status', 'StoreCoupon/status')->option(['real_name' => '修改优惠券状态']);
        Route::delete('coupon/:id', 'StoreCoupon/delete')->option(['real_name' => '删除优惠券']);

        //用户等级
        Route::get('user_level/list', 'UserLevel/lst')->option(['real_name' => '用户等级列表']);

        //用户
        Route::get('user/list', 'User/lst')->option(['real_name' => '用户列表']);
        Route::post('user', 'User/save')->option(['real_name' => '新增用户']);
        Route::put('user/:uid', 'User/update')->option(['real_name' => '修改用户']);
        Route::put('user/give/:uid', 'User/give')->option(['real_name' => '赠送积分/金额']);

    })->middleware(AuthTokenMiddleware::class);

})->middleware(AllowOriginMiddleware::class);

Route::miss(function () {
    if (app()->request->isOptions()) {
        $header = Config::get('cookie.header');
        $header['Access-Control-Allow-Origin'] = app()->request->header('origin');
        return Response::create('ok')->code(200)->header($header);
    } else
        return Response::create()->code(404);
});
