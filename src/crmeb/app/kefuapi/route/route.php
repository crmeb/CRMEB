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
use app\http\middleware\AllowOriginMiddleware;
use app\kefuapi\middleware\KefuAuthTokenMiddleware;
use think\facade\Config;
use think\facade\Route;
use think\Response;

Route::any('ticket/[:appid]', 'Login/ticket');
Route::get('test', 'Login/test');

Route::group(function () {

    Route::post('login', 'Login/login')->name('kefuLogin');//账号登录
    Route::get('key', 'Login/getLoginKey')->name('getLoginKey');//获取扫码登录key
    Route::get('scan/:key', 'Login/scanLogin')->name('scanLogin');//检测扫码情况
    Route::get('config', 'Login/getAppid')->name('getAppid');//获取配置
    Route::get('wechat', 'Login/wechatAuth')->name('wechatAuth');//微信扫码登录

    Route::group(function () {

        Route::post('upload', 'User/upload')->name('upload');//上传图片

    })->middleware(KefuAuthTokenMiddleware::class);

    Route::group('user', function () {

        Route::get('record', 'User/recordList')->name('recordList');//和客服聊天过的用户
        Route::get('info/:uid', 'User/userInfo')->name('getUserInfo');//用户详细信息
        Route::get('label/:uid', 'User/getUserLabel')->name('getUserLabel');//用户标签
        Route::put('label/:uid', 'User/setUserLabel')->name('setUserLabel');//设置用户标签
        Route::get('group', 'User/getUserGroup')->name('getUserGroup');//退出登录
        Route::put('group/:uid/:id', 'User/setUserGroup')->name('setUserGroup');//退出登录
        Route::post('logout', 'User/logout')->name('logout');//退出登录

    })->middleware(KefuAuthTokenMiddleware::class);

    Route::group('order', function () {

        Route::get('list/:uid', 'Order/getUserOrderList')->name('getUserOrderList');//订单列表
        Route::post('delivery/:id', 'Order/delivery_keep')->name('orderDeliveryKeep');//订单发货
        Route::put('update/:id', 'Order/update')->name('orderUpdate');//订单修改
        Route::post('refund', 'Order/refund')->name('orderRefund');//订单退款
        Route::get('refund_form/:id', 'Order/refundForm')->name('orderRefund');//订单退款
        Route::get('edit/:id', 'Order/edit')->name('orderEdit');//订单退款
        Route::post('remark', 'Order/remark')->name('remark');//订单备注
        Route::get('info/:id', 'Order/orderInfo')->name('orderInfo');//获取订单详情
        Route::get('export', 'Order/export')->name('export');//获取订单详情
        Route::get('temp', 'Order/getExportTemp')->name('getExportTemp');//获取物流公司模板
        Route::get('delivery_all', 'Order/getDeliveryAll')->name('getDeliveryAll');//获取配送员列表全部
        Route::get('delivery_info', 'Order/getDeliveryInfo')->name('getDeliveryInfo');//获取配送员列表全部
        Route::get('verific/:id', 'Order/order_verific')->name('orderVerific');//单个订单号进行核销

    })->middleware(KefuAuthTokenMiddleware::class);

    Route::group('product', function () {

        Route::get('hot/:uid', 'Product/getProductHotSale')->name('getProductHotSale');//热销商品
        Route::get('visit/:uid', 'Product/getVisitProductList')->name('getVisitProductList');//商品足记
        Route::get('cart/:uid', 'Product/getCartProductList')->name('getCartProductList');//购买记录
        Route::get('info/:id', 'Product/getProductInfo')->name('getProductInfo');//商品详情

    })->middleware(KefuAuthTokenMiddleware::class);

    Route::group('service', function () {

        Route::get('list', 'Service/getChatList')->name('getChatList');//聊天记录
        Route::get('info', 'Service/getServiceInfo')->name('getServiceInfo');//客服详细信息
        Route::get('speechcraft', 'Service/getSpeechcraftList')->name('getSpeechcraftList');//客服话术
        Route::post('transfer', 'Service/transfer')->name('transfer');//客服转接
        Route::get('transfer_list', 'Service/getServiceList')->name('getServiceList');//客服转接
        Route::get('cate', 'Service/getCateList')->name('getCateList');//分类列表
        Route::post('cate', 'Service/saveCate')->name('saveCate');//保存分类
        Route::put('cate/:id', 'Service/editCate')->name('editCate');//编辑分类
        Route::delete('cate/:id', 'Service/deleteCate')->name('deleteCate');//删除分类
        Route::post('speechcraft', 'Service/saveSpeechcraft')->name('saveSpeechcraft');//添加话术
        Route::put('speechcraft/:id', 'Service/editSpeechcraft')->name('editSpeechcraft');//修改话术
        Route::delete('speechcraft/:id', 'Service/deleteSpeechcraft')->name('deleteSpeechcraft');//删除话术

    })->middleware(KefuAuthTokenMiddleware::class);

    Route::group('tourist', function () {
        Route::get('user', 'Common/getServiceUser')->name('getServiceUser');//随机客服信息
        Route::get('adv', 'Common/getKfAdv')->name('getKfAdv');//获取客服广告
        Route::post('feedback', 'Common/saveFeedback')->name('saveFeedback');//保存客服反馈内容
        Route::get('feedback', 'Common/getFeedbackInfo')->name('getFeedbackInfo');//获取反馈页面广告位内容
        Route::get('order/:order_id', 'Common/getOrderInfo')->name('getOrderInfo');//获取订单信息
        Route::get('product/:id', 'Common/getProductInfo')->name('getProductInfo');//获取商品信息
        Route::get('chat', 'Common/getChatList')->name('getChatList');//获取聊天记录
        Route::post('upload', 'Common/upload')->name('upload');//图片上传
    });

})->middleware(AllowOriginMiddleware::class);

Route::miss(function () {
    if (app()->request->isOptions()) {
        $header = Config::get('cookie.header');
        $header['Access-Control-Allow-Origin'] = app()->request->header('origin');
        return Response::create('ok')->code(200)->header($header);
    } else
        return Response::create()->code(404);
});
