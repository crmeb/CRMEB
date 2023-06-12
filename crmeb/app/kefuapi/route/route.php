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
use app\http\middleware\AllowOriginMiddleware;
use app\kefuapi\middleware\KefuAuthTokenMiddleware;
use think\facade\Config;
use think\facade\Route;
use think\Response;

Route::group(function () {

    Route::group(function () {
        Route::post('login', 'Login/login')->name('kefuLogin')->option(['real_name' => '账号登录']);//账号登录
        Route::get('key', 'Login/getLoginKey')->name('getLoginKey')->option(['real_name' => '获取扫码登录key']);//获取扫码登录key
        Route::get('scan/:key', 'Login/scanLogin')->name('scanLogin')->option(['real_name' => '检测扫码情况']);//检测扫码情况
        Route::get('config', 'Login/getAppid')->name('getAppid')->option(['real_name' => '获取配置']);//获取配置
        Route::get('wechat', 'Login/wechatAuth')->name('wechatAuth')->option(['real_name' => '微信扫码登录']);//微信扫码登录
    })->option(['mark' => 'login', 'mark_name' => '登录']);


    Route::group(function () {

        Route::post('upload', 'User/upload')->name('upload')->option(['real_name' => '上传图片', 'mark' => 'common', 'mark_name' => '公用接口']);//上传图片

    })->middleware(KefuAuthTokenMiddleware::class);

    Route::group('user', function () {

        Route::get('record', 'User/recordList')->name('recordList')->option(['real_name' => '和客服聊天过的用户']);//和客服聊天过的用户
        Route::get('info/:uid', 'User/userInfo')->name('getUserInfo')->option(['real_name' => '用户详细信息']);//用户详细信息
        Route::get('label/:uid', 'User/getUserLabel')->name('getUserLabel')->option(['real_name' => '用户标签']);//用户标签
        Route::put('label/:uid', 'User/setUserLabel')->name('setUserLabel')->option(['real_name' => '设置用户标签']);//设置用户标签
        Route::get('group', 'User/getUserGroup')->name('getUserGroup')->option(['real_name' => '获取用户分组']);//退出登录
        Route::put('group/:uid/:id', 'User/setUserGroup')->name('setUserGroup')->option(['real_name' => '设置用户分组']);//退出登录
        Route::post('logout', 'User/logout')->name('logout')->option(['real_name' => '退出登录']);//退出登录

    })->middleware(KefuAuthTokenMiddleware::class)
        ->option(['mark' => 'user', 'mark_name' => '用户']);

    Route::group('order', function () {

        Route::get('list/:uid', 'Order/getUserOrderList')->name('getUserOrderList')->option(['real_name' => '订单列表']);//订单列表
        Route::post('delivery/:id', 'Order/delivery_keep')->name('orderDeliveryKeep')->option(['real_name' => '订单发货']);//订单发货
        Route::put('update/:id', 'Order/update')->name('orderUpdate')->option(['real_name' => '订单修改']);//订单修改
        Route::post('refund', 'Order/refund')->name('orderRefund')->option(['real_name' => '订单退款']);//订单退款
        Route::get('refund_form/:id', 'Order/refundForm')->name('orderRefund')->option(['real_name' => '订单退款']);//订单退款
        Route::get('edit/:id', 'Order/edit')->name('orderEdit')->option(['real_name' => '订单退款']);//订单退款
        Route::post('remark', 'Order/remark')->name('remark')->option(['real_name' => '订单备注']);//订单备注
        Route::get('info/:id', 'Order/orderInfo')->name('orderInfo')->option(['real_name' => '获取订单详情']);//获取订单详情
        Route::get('export', 'Order/export')->name('export')->option(['real_name' => '获取订单详情']);//获取订单详情
        Route::get('temp', 'Order/getExportTemp')->name('getExportTemp')->option(['real_name' => '获取物流公司模板']);//获取物流公司模板
        Route::get('delivery_all', 'Order/getDeliveryAll')->name('getDeliveryAll')->option(['real_name' => '获取配送员列表全部']);//获取配送员列表全部
        Route::get('delivery_info', 'Order/getDeliveryInfo')->name('getDeliveryInfo')->option(['real_name' => '获取配送员列表全部']);//获取配送员列表全部
        Route::get('verific/:id', 'Order/order_verific')->name('orderVerific')->option(['real_name' => '单个订单号进行核销']);//单个订单号进行核销

    })->middleware(KefuAuthTokenMiddleware::class)
        ->option(['mark' => 'order', 'mark_name' => '订单']);

    Route::group('product', function () {

        Route::get('hot/:uid', 'Product/getProductHotSale')->name('getProductHotSale')->option(['real_name' => '热销商品']);//热销商品
        Route::get('visit/:uid', 'Product/getVisitProductList')->name('getVisitProductList')->option(['real_name' => '商品足记']);//商品足记
        Route::get('cart/:uid', 'Product/getCartProductList')->name('getCartProductList')->option(['real_name' => '购买记录']);//购买记录
        Route::get('info/:id', 'Product/getProductInfo')->name('getProductInfo')->option(['real_name' => '商品详情']);//商品详情

    })->middleware(KefuAuthTokenMiddleware::class)
        ->option(['mark' => 'service', 'mark_name' => '商品']);

    Route::group('service', function () {

        Route::get('list', 'Service/getChatList')->name('getChatList')->option(['real_name' => '聊天记录']);//聊天记录
        Route::get('info', 'Service/getServiceInfo')->name('getServiceInfo')->option(['real_name' => '客服详细信息']);//客服详细信息
        Route::get('speechcraft', 'Service/getSpeechcraftList')->name('getSpeechcraftList')->option(['real_name' => '客服话术']);//客服话术
        Route::post('transfer', 'Service/transfer')->name('transfer')->option(['real_name' => '客服转接']);//客服转接
        Route::get('transfer_list', 'Service/getServiceList')->name('getServiceList')->option(['real_name' => '客服转接']);//客服转接
        Route::get('cate', 'Service/getCateList')->name('getCateList')->option(['real_name' => '分类列表']);//分类列表
        Route::post('cate', 'Service/saveCate')->name('saveCate')->option(['real_name' => '保存分类']);//保存分类
        Route::put('cate/:id', 'Service/editCate')->name('editCate')->option(['real_name' => '编辑分类']);//编辑分类
        Route::delete('cate/:id', 'Service/deleteCate')->name('deleteCate')->option(['real_name' => '删除分类']);//删除分类
        Route::post('speechcraft', 'Service/saveSpeechcraft')->name('saveSpeechcraft')->option(['real_name' => '添加话术']);//添加话术
        Route::put('speechcraft/:id', 'Service/editSpeechcraft')->name('editSpeechcraft')->option(['real_name' => '修改话术']);//修改话术
        Route::delete('speechcraft/:id', 'Service/deleteSpeechcraft')->name('deleteSpeechcraft')->option(['real_name' => '删除话术']);//删除话术

    })->middleware(KefuAuthTokenMiddleware::class)
        ->option(['mark' => 'service', 'mark_name' => '客服']);

    Route::group('tourist', function () {
        Route::get('user', 'Common/getServiceUser')->name('getServiceUser')->option(['real_name' => '随机客服信息']);//随机客服信息
        Route::get('adv', 'Common/getKfAdv')->name('getKfAdv')->option(['real_name' => '获取客服广告']);//获取客服广告
        Route::post('feedback', 'Common/saveFeedback')->name('saveFeedback')->option(['real_name' => '保存客服反馈内容']);//保存客服反馈内容
        Route::get('feedback', 'Common/getFeedbackInfo')->name('getFeedbackInfo')->option(['real_name' => '获取反馈页面广告位内容']);//获取反馈页面广告位内容
        Route::get('order/:order_id', 'Common/getOrderInfo')->name('getOrderInfo')->option(['real_name' => '获取订单信息']);//获取订单信息
        Route::get('product/:id', 'Common/getProductInfo')->name('getProductInfo')->option(['real_name' => '获取商品信息']);//获取商品信息
        Route::get('chat', 'Common/getChatList')->name('getChatList')->option(['real_name' => '获取聊天记录']);//获取聊天记录
        Route::post('upload', 'Common/upload')->name('upload')->option(['real_name' => '图片上传']);//图片上传
    })->option(['mark' => 'tourist', 'mark_name' => '游客客服']);

})->middleware(AllowOriginMiddleware::class);

Route::miss(function () {
    if (app()->request->isOptions()) {
        $header = Config::get('cookie.header');
        $header['Access-Control-Allow-Origin'] = app()->request->header('origin');
        return Response::create('ok')->code(200)->header($header);
    } else
        return Response::create()->code(404);
});
