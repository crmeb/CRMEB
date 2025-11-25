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
use think\facade\Config;
use think\Response;

Route::group(function () {
    Route::any('wechat/serve', 'v1.wechat.WechatController/serve')->option(['real_name' => '公众号服务']);//公众号服务
    Route::any('wechat/miniServe', 'v1.wechat.WechatController/miniServe')->option(['real_name' => '小程序服务']);//公众号服务
    Route::any('pay/notify/:type', 'v1.PayController/notify')->option(['real_name' => '支付回调']);//支付回调
    Route::any('transfer/notify/:type', 'v1.PayController/transferNotify')->option(['real_name' => '商户转账回调']);//商户转账回调
    Route::any('order_call_back', 'v1.order.StoreOrderController/callBack')->option(['real_name' => '商家寄件回调']);//商家寄件回调
    Route::get('get_script', 'v1.PublicController/getScript')->option(['real_name' => '移动端自定义JS']);//移动端自定义JS
    Route::get('custom_pc_js', 'v1.PublicController/customPcJs')->option(['real_name' => 'PC端自定义JS']);//PC端自定义JS
    Route::get('version', 'v1.PublicController/getVersion')->option(['real_name' => '获取代码版本号']);
    Route::get('service_pay_result', 'v1.PublicController/servicePayResult')->option(['real_name' => '服务商支付商家小票接口']);
})->middleware(\app\http\middleware\AllowOriginMiddleware::class)->option(['mark' => 'serve', 'mark_name' => '服务接口']);

Route::group(function () {
    //apple快捷登陆
    Route::post('apple_login', 'v1.LoginController/appleLogin')->name('appleLogin')->option(['real_name' => '微信APP授权']);//微信APP授权
    //账号密码登录
    Route::post('login', 'v1.LoginController/login')->name('login')->option(['real_name' => '账号密码登录']);
    // 获取发短信的key
    Route::get('verify_code', 'v1.LoginController/verifyCode')->name('verifyCode')->option(['real_name' => '获取发短信的key']);
    //手机号登录
    Route::post('login/mobile', 'v1.LoginController/mobile')->name('loginMobile')->option(['real_name' => '手机号登录']);
    //图片验证码
    Route::get('sms_captcha', 'v1.LoginController/captcha')->name('captcha')->option(['real_name' => '图片验证码']);
    //图形验证码
    Route::get('ajcaptcha', 'v1.LoginController/ajcaptcha')->name('ajcaptcha')->option(['real_name' => '图形验证码']);
    //图形验证码验证
    Route::post('ajcheck', 'v1.LoginController/ajcheck')->name('ajcheck')->option(['real_name' => '图形验证码验证']);
    //手机验证码发送
    Route::post('register/verify', 'v1.LoginController/verify')->name('registerVerify')->option(['real_name' => '手机验证码发送']);
    //手机号注册
    Route::post('register', 'v1.LoginController/register')->name('register')->option(['real_name' => '手机号注册']);
    //手机号修改密码
    Route::post('register/reset', 'v1.LoginController/reset')->name('registerReset')->option(['real_name' => '手机号修改密码']);
    // 绑定手机号(静默授权 还未有用户信息)
    Route::post('binding', 'v1.LoginController/binding_phone')->name('bindingPhone')->option(['real_name' => '绑定手机号']);
    // 支付宝复制链接支付 弃用
//    Route::get('ali_pay', 'v1.order.StoreOrderController/aliPay')->name('aliPay');
    //查询版权
    Route::get('copyright', 'v1.PublicController/copyright')->option(['real_name' => '申请版权'])->option(['real_name' => '查询版权']);
    //商城基础配置汇总接口
    Route::get('basic_config', 'v1.PublicController/getMallBasicConfig')->option(['real_name' => '商城基础配置汇总接口']);
    //小程序跳转url接口
    Route::get('get_scheme_url/:id', 'v1.PublicController/getSchemeUrl')->option(['real_name' => '小程序跳转url接口']);
    //远程注册用户
    Route::get('remote_register', 'v1.LoginController/remoteRegister')->option(['real_name' => '远程注册用户']);

})->middleware(\app\http\middleware\AllowOriginMiddleware::class)
    ->middleware(\app\api\middleware\StationOpenMiddleware::class)
    ->option(['mark' => 'base', 'mark_name' => '基础接口']);


//管理员订单操作类
Route::group(function () {
    Route::get('admin/order/statistics', 'v1.admin.StoreOrderController/statistics')->name('adminOrderStatistics')->option(['real_name' => '订单数据统计']);//订单数据统计
    Route::get('admin/order/data', 'v1.admin.StoreOrderController/data')->name('adminOrderData')->option(['real_name' => '订单每月统计数据']);//订单每月统计数据
    Route::get('admin/order/list', 'v1.admin.StoreOrderController/lst')->name('adminOrderList')->option(['real_name' => '订单列表']);//订单列表
    Route::get('admin/refund_order/list', 'v1.admin.StoreOrderController/refundOrderList')->name('adminOrderRefundList')->option(['real_name' => '退款订单列表']);//退款订单列表
    Route::get('admin/order/detail/:orderId', 'v1.admin.StoreOrderController/detail')->name('adminOrderDetail')->option(['real_name' => '订单详情']);//订单详情
    Route::get('admin/refund_order/detail/:uni', 'v1.admin.StoreOrderController/refundOrderDetail')->name('RefundOrderDetail')->option(['real_name' => '退款订单详情']);//退款订单详情
    Route::get('admin/order/delivery/gain/:orderId', 'v1.admin.StoreOrderController/delivery_gain')->name('adminOrderDeliveryGain')->option(['real_name' => '订单发货获取订单信息']);//订单发货获取订单信息
    Route::post('admin/order/delivery/keep/:id', 'v1.admin.StoreOrderController/delivery_keep')->name('adminOrderDeliveryKeep')->option(['real_name' => '订单发货']);//订单发货
    Route::post('admin/order/price', 'v1.admin.StoreOrderController/price')->name('adminOrderPrice')->option(['real_name' => '订单改价']);//订单改价
    Route::post('admin/order/remark', 'v1.admin.StoreOrderController/remark')->name('adminOrderRemark')->option(['real_name' => '订单备注']);//订单备注
    Route::post('admin/order/agreeExpress', 'v1.admin.StoreOrderController/agreeExpress')->name('adminOrderAgreeExpress')->option(['real_name' => '订单同意退货']);//订单同意退货
    Route::post('admin/refund_order/remark', 'v1.admin.StoreOrderController/refundRemark')->name('refundRemark')->option(['real_name' => '退款订单备注']);//退款订单备注
    Route::get('admin/order/time', 'v1.admin.StoreOrderController/time')->name('adminOrderTime')->option(['real_name' => '订单交易额时间统计']);//订单交易额时间统计
    Route::post('admin/order/offline', 'v1.admin.StoreOrderController/offline')->name('adminOrderOffline')->option(['real_name' => '订单支付']);//订单支付
    Route::post('admin/order/refund', 'v1.admin.StoreOrderController/refund')->name('adminOrderRefund')->option(['real_name' => '订单退款']);//订单退款
    Route::post('order/order_verific', 'v1.admin.StoreOrderController/order_verific')->name('order')->option(['real_name' => '订单核销']);//订单核销
    Route::get('admin/order/delivery', 'v1.admin.StoreOrderController/getDeliveryAll')->name('getDeliveryAll')->option(['real_name' => '获取配送员']);//获取配送员
    Route::get('admin/order/delivery_info', 'v1.admin.StoreOrderController/getDeliveryInfo')->name('getDeliveryInfo')->option(['real_name' => '获取电子面单默认信息']);//获取电子面单默认信息
    Route::get('admin/order/export_temp', 'v1.admin.StoreOrderController/getExportTemp')->name('getExportTemp')->option(['real_name' => '获取电子面单模板获取']);//获取电子面单模板获取
    Route::get('admin/order/export_all', 'v1.admin.StoreOrderController/getExportAll')->name('getExportAll')->option(['real_name' => '获取物流公司']);//获取物流公司
    Route::get('admin/order/express/:uni/[:type]', 'v1.admin.StoreOrderController/express')->name('orderExpress')->option(['real_name' => '订单查看物流']); //订单查看物流
})->middleware(\app\http\middleware\AllowOriginMiddleware::class)
    ->middleware(\app\api\middleware\StationOpenMiddleware::class)
    ->middleware(\app\api\middleware\AuthTokenMiddleware::class, true)
    ->middleware(\app\api\middleware\CustomerMiddleware::class)
    ->option(['mark' => 'admin', 'mark_name' => '移动端订单管理']);;

//会员授权接口
Route::group(function () {
    Route::group(function () {
        //获取支付方式
        Route::get('pay/config', 'v1.PayController/config')->name('payConfig')->option(['real_name' => '获取支付方式']);
        //用户修改手机号
        Route::post('user/updatePhone', 'v1.LoginController/update_binding_phone')->name('updateBindingPhone')->option(['real_name' => '用户修改手机号']);
        //设置登录code
        Route::post('user/code', 'v1.user.StoreService/setLoginCode')->name('setLoginCode')->option(['real_name' => '设置登录code']);
        //查看code是否可用
        Route::get('user/code', 'v1.LoginController/setLoginKey')->name('getLoginKey')->option(['real_name' => '查看code是否可用']);
        //用户绑定手机号
        Route::post('user/binding', 'v1.LoginController/user_binding_phone')->name('userBindingPhone')->option(['real_name' => '用户绑定手机号']);
        Route::get('logout', 'v1.LoginController/logout')->name('logout')->option(['real_name' => '退出登录']);// 退出登录
        Route::post('switch_h5', 'v1.LoginController/switch_h5')->name('switch_h5')->option(['real_name' => '切换账号']);// 切换账号
        //公共类
        Route::post('upload/image', 'v1.PublicController/upload_image')->name('uploadImage')->option(['real_name' => '图片上传']);//图片上传
        // 用户微信转账详情接口
        Route::get('transfer/info', 'v1.PublicController/getTransferInfo')->name('getTransferInfo')->option(['real_name' => '用户微信转账详情接口']);// 用户微信转账详情接口

    })->option(['mark' => 'common', 'mark_name' => '公共接口']);

    Route::group(function () {
        //用户类 客服聊天记录
        Route::get('user/service/list', 'v1.user.StoreService/lst')->name('userServiceList')->option(['real_name' => '客服列表']);//客服列表
        Route::get('user/service/record', 'v1.user.StoreService/record')->name('userServiceRecord')->option(['real_name' => '客服聊天记录']);//客服聊天记录
        Route::post('user/service/feedback', 'v1.user.StoreService/saveFeedback')->name('saveFeedback')->option(['real_name' => '保存客服反馈信息']);//保存客服反馈信息
        Route::get('user/service/feedback', 'v1.user.StoreService/getFeedbackInfo')->name('getFeedbackInfo')->option(['real_name' => '获得客服反馈头部信息']);//获得客服反馈头部信息
        Route::get('user/service/get_adv', 'v1.user.StoreService/getKfAdv')->name('userServiceGetKfAdv')->option(['real_name' => '获取客服页面广告']);//获取客服页面广告
    })->option(['parent' => 'user', 'cate_name' => '客服']);

    Route::group(function () {
        //用户类  用户coupons/order
        Route::get('user', 'v1.user.UserController/user')->name('user')->option(['real_name' => '个人中心']);//个人中心
        Route::post('user/spread', 'v1.user.UserController/spread')->name('userSpread')->option(['real_name' => '静默绑定授权']);//静默绑定授权
        Route::post('user/edit', 'v1.user.UserController/edit')->name('userEdit')->option(['real_name' => '用户修改信息']);//用户修改信息
        Route::get('user/balance', 'v1.user.UserController/balance')->name('userBalance')->option(['real_name' => '用户资金统计']);//用户资金统计
        Route::get('userinfo', 'v1.user.UserController/userinfo')->name('userinfo')->option(['real_name' => '用户信息']);// 用户信息
    })->option(['parent' => 'user', 'cate_name' => '用户中心']);

    Route::group(function () {
        //用户类  地址
        Route::get('address/detail/:id', 'v1.user.UserAddressController/address')->name('address')->option(['real_name' => '获取单个地址']);//获取单个地址
        Route::get('address/list', 'v1.user.UserAddressController/address_list')->name('addressList')->option(['real_name' => '地址列表']);//地址列表
        Route::post('address/default/set', 'v1.user.UserAddressController/address_default_set')->name('addressDefaultSet')->option(['real_name' => '设置默认地址']);//设置默认地址
        Route::get('address/default', 'v1.user.UserAddressController/address_default')->name('addressDefault')->option(['real_name' => '获取默认地址']);//获取默认地址
        Route::post('address/edit', 'v1.user.UserAddressController/address_edit')->name('addressEdit')->option(['real_name' => '修改/添加地址']);//修改 添加 地址
        Route::post('address/del', 'v1.user.UserAddressController/address_del')->name('addressDel')->option(['real_name' => '删除地址']);//删除地址
    })->option(['parent' => 'user', 'cate_name' => '用户地址']);

    Route::group(function () { //用户类 收藏
        Route::get('collect/user', 'v1.user.UserCollectController/collect_user')->name('collectUser')->option(['real_name' => '收藏商品列表']);//收藏商品列表
        Route::post('collect/add', 'v1.user.UserCollectController/collect_add')->name('collectAdd')->option(['real_name' => '添加收藏']);//添加收藏
        Route::post('collect/del', 'v1.user.UserCollectController/collect_del')->name('collectDel')->option(['real_name' => '取消收藏']);//取消收藏
        Route::post('collect/all', 'v1.user.UserCollectController/collect_all')->name('collectAll')->option(['real_name' => '批量添加收藏']);//批量添加收藏
    })->option(['parent' => 'user', 'cate_name' => '用户收藏']);

    Route::group(function () {
        Route::get('rank', 'v1.user.UserController/rank')->name('rank')->option(['real_name' => '公众号授权登录']);//推广人排行
        //用戶类 分享
        Route::post('user/share', 'v1.PublicController/user_share')->name('user_share')->option(['real_name' => '记录用户分享']);//记录用户分享
        Route::get('user/share/words', 'v1.PublicController/copy_share_words')->name('user_share_words')->option(['real_name' => '关键字分享']);//关键字分享
    })->option(['parent' => 'user', 'cate_name' => '用户分享']);

    Route::group(function () {
        //用户类 签到
        Route::get('sign/config', 'v1.user.UserSignController/sign_config')->name('signConfig')->option(['real_name' => '签到配置']);//签到配置
        Route::get('sign/list', 'v1.user.UserSignController/sign_list')->name('signList')->option(['real_name' => '签到列表']);//签到列表
        Route::get('sign/month', 'v1.user.UserSignController/sign_month')->name('signIntegral')->option(['real_name' => '签到列表（年月）']);//签到列表（年月）
        Route::get('sign/remind/:status', 'v1.user.UserSignController/sign_remind')->name('signRemind')->option(['real_name' => '签到提醒开关']);//签到列表（年月）
        Route::post('sign/user', 'v1.user.UserSignController/sign_user')->name('signUser')->option(['real_name' => '签到用户信息']);//签到用户信息
        Route::post('sign/integral', 'v1.user.UserSignController/sign_integral')->name('signIntegral')->option(['real_name' => '公众号授权登录'])->middleware(BlockerMiddleware::class);//签到
    })->option(['mark' => 'sign', 'mark_name' => '签到']);

    Route::group(function () {
        //优惠券类
        Route::post('coupon/receive', 'v1.store.StoreCouponsController/receive')->name('couponReceive')->option(['real_name' => '领取优惠券']); //领取优惠券
        Route::post('coupon/receive/batch', 'v1.store.StoreCouponsController/receive_batch')->name('couponReceiveBatch')->option(['real_name' => '批量领取优惠券']); //批量领取优惠券
        Route::get('coupons/user/:types', 'v1.store.StoreCouponsController/user')->name('couponsUser')->option(['real_name' => '用户已领取优惠券']);//用户已领取优惠券
        Route::get('coupons/order/:price', 'v1.store.StoreCouponsController/order')->name('couponsOrder')->option(['real_name' => '优惠券 订单列表']);//优惠券 订单列表
    })->option(['mark' => 'coupons', 'mark_name' => '优惠券']);

    Route::group(function () {
        //购物车类
        Route::get('cart/list', 'v1.store.StoreCartController/lst')->name('cartList')->option(['real_name' => '购物车列表']); //购物车列表
        Route::post('cart/add', 'v1.store.StoreCartController/add')->name('cartAdd')->option(['real_name' => '购物车添加']); //购物车添加
        Route::post('cart/del', 'v1.store.StoreCartController/del')->name('cartDel')->option(['real_name' => '购物车删除']); //购物车删除
        Route::post('order/cancel', 'v1.order.StoreOrderController/cancel')->name('orderCancel')->option(['real_name' => '订单取消']); //订单取消
        Route::post('cart/num', 'v1.store.StoreCartController/num')->name('cartNum')->option(['real_name' => '购物车修改商品数量']); //购物车 修改商品数量
        Route::get('cart/count', 'v1.store.StoreCartController/count')->name('cartCount')->option(['real_name' => '购物车数量']); //购物车 获取数量
    })->option(['mark' => 'cart', 'mark_name' => '购物车']);

    Route::group(function () {
        //订单类
        Route::post('order/check_shipping', 'v1.order.StoreOrderController/checkShipping')->name('checkShipping')->option(['real_name' => '检测是否显示快递和自提标签']); //检测是否显示快递和自提标签
        Route::post('order/confirm', 'v1.order.StoreOrderController/confirm')->name('orderConfirm')->option(['real_name' => '订单确认']); //订单确认
        Route::post('order/computed/:key', 'v1.order.StoreOrderController/computedOrder')->name('computedOrder')->option(['real_name' => '计算订单金额']); //计算订单金额
        Route::post('order/create/:key', 'v1.order.StoreOrderController/create')->name('orderCreate')->middleware(BlockerMiddleware::class)->option(['real_name' => '订单创建']); //订单创建
        Route::get('order/data', 'v1.order.StoreOrderController/data')->name('orderData')->option(['real_name' => '订单统计数据']); //订单统计数据
        Route::get('order/list', 'v1.order.StoreOrderController/lst')->name('orderList')->option(['real_name' => '订单列表']); //订单列表
        Route::get('order/detail/:uni/[:cartId]', 'v1.order.StoreOrderController/detail')->name('orderDetail')->option(['real_name' => '订单详情']); //订单详情
        Route::get('order/refund_detail/:uni/[:cartId]', 'v1.order.StoreOrderController/refund_detail')->name('refundDetail')->option(['real_name' => '退款订单详情']); //退款订单详情
        Route::get('order/refund/reason', 'v1.order.StoreOrderController/refund_reason')->name('orderRefundReason')->middleware(BlockerMiddleware::class)->option(['real_name' => '订单退款理由']); //订单退款理由
        Route::post('order/refund/verify', 'v1.order.StoreOrderController/refund_verify')->name('orderRefundVerify')->middleware(BlockerMiddleware::class)->option(['real_name' => '订单退款审核']); //订单退款审核
        Route::post('order/take', 'v1.order.StoreOrderController/take')->name('orderTake')->middleware(BlockerMiddleware::class)->option(['real_name' => '订单收货']); //订单收货
        Route::get('order/express/:uni/[:type]', 'v1.order.StoreOrderController/express')->name('orderExpress')->option(['real_name' => '订单查看物流']); //订单查看物流
        Route::post('order/del', 'v1.order.StoreOrderController/del')->name('orderDel')->option(['real_name' => '订单删除']); //订单删除
        Route::post('order/again', 'v1.order.StoreOrderController/again')->name('orderAgain')->option(['real_name' => '再次下单']); //订单 再次下单
        Route::post('order/pay', 'v1.order.StoreOrderController/pay')->name('orderPay')->option(['real_name' => '订单支付']); //订单支付
        Route::post('order/product', 'v1.order.StoreOrderController/product')->name('orderProduct')->option(['real_name' => '订单商品信息']); //订单商品信息
        Route::post('order/comment', 'v1.order.StoreOrderController/comment')->name('orderComment')->option(['real_name' => '订单评价']); //订单评价
        Route::get('order/cashier/:orderId/[:type]', 'v1.order.StoreOrderController/cashier')->name('orderCashier')->option(['real_name' => '订单收银台']); //订单收银台
        Route::get('order/friend_detail', 'v1.order.StoreOrderController/friendDetail')->name('friendDetail')->option(['real_name' => '代付详情']);//代付详情
        Route::post('order/receive_gift/:oid', 'v1.order.StoreOrderController/receiveGift')->name('receiveGift')->option(['real_name' => '领取礼物']);//领取礼物
        Route::get('order/gift_detail/:oid', 'v1.order.StoreOrderController/giftDetail')->name('giftDetail')->option(['real_name' => '礼品详情']); //礼品详情

    })->option(['mark' => 'order', 'mark_name' => '订单']);

    Route::group(function () {
        //活动---砍价
        Route::get('bargain/detail/:id', 'v1.activity.StoreBargainController/detail')->name('bargainDetail')->option(['real_name' => '砍价商品详情']);//砍价商品详情
        Route::post('bargain/start', 'v1.activity.StoreBargainController/start')->name('bargainStart')->option(['real_name' => '砍价开启']);//砍价开启
        Route::post('bargain/start/user', 'v1.activity.StoreBargainController/start_user')->name('bargainStartUser')->option(['real_name' => '砍价用户信息']);//砍价 开启砍价用户信息
        Route::post('bargain/share', 'v1.activity.StoreBargainController/share')->name('bargainShare')->option(['real_name' => '砍价分享']);//砍价 观看/分享/参与次数
        Route::post('bargain/help', 'v1.activity.StoreBargainController/help')->name('bargainHelp')->option(['real_name' => '帮好友砍价']);//砍价 帮助好友砍价
        Route::post('bargain/help/price', 'v1.activity.StoreBargainController/help_price')->name('bargainHelpPrice')->option(['real_name' => '砍价砍掉金额']);//砍价 砍掉金额
        Route::post('bargain/help/count', 'v1.activity.StoreBargainController/help_count')->name('bargainHelpCount')->option(['real_name' => '砍价帮统计']);//砍价 砍价帮总人数、剩余金额、进度条、已经砍掉的价格
        Route::post('bargain/help/list', 'v1.activity.StoreBargainController/help_list')->name('bargainHelpList')->option(['real_name' => '砍价 砍价帮']);//砍价 砍价帮
        Route::post('bargain/poster', 'v1.activity.StoreBargainController/poster')->name('bargainPoster')->option(['real_name' => '砍价海报']);//砍价海报
        Route::get('bargain/user/list', 'v1.activity.StoreBargainController/user_list')->name('bargainUserList')->option(['real_name' => '砍价列表']);//砍价列表(已参与)
        Route::post('bargain/user/cancel', 'v1.activity.StoreBargainController/user_cancel')->name('bargainUserCancel')->option(['real_name' => '砍价取消']);//砍价取消
        Route::get('bargain/poster_info/:bargainId', 'v1.activity.StoreBargainController/posterInfo')->name('posterInfo')->option(['real_name' => '砍价海报详细信息']);//砍价海报详细信息
    })->option(['parent' => 'activity_nologin', 'cate_name' => '砍价']);

    Route::group(function () {
        //活动---拼团
        Route::get('combination/pink/:id', 'v1.activity.StoreCombinationController/pink')->name('combinationPink')->option(['real_name' => '拼团开团']);//拼团开团
        Route::post('combination/remove', 'v1.activity.StoreCombinationController/remove')->name('combinationRemove')->option(['real_name' => '拼团 取消开团']);//拼团 取消开团
        Route::post('combination/poster', 'v1.activity.StoreCombinationController/poster')->name('combinationPoster')->option(['real_name' => '拼团海报']);//拼团海报
        Route::get('combination/poster_info/:id', 'v1.activity.StoreCombinationController/posterInfo')->name('pinkPosterInfo')->option(['real_name' => '拼团海报详细获取']);//拼团海报详细获取
        Route::get('combination/code/:id', 'v1.activity.StoreCombinationController/code')->name('combinationCode')->option(['real_name' => '拼团商品海报']);//拼团商品海报
        Route::get('seckill/code/:id', 'v1.activity.StoreSeckillController/code')->name('seckillCode')->option(['real_name' => '秒杀商品海报']);//秒杀商品海报
    })->option(['parent' => 'activity_nologin', 'cate_name' => '拼团']);;

    Route::group(function () {
        //账单类
        Route::post('spread/people', 'v1.user.UserController/spread_people')->name('spreadPeople')->option(['real_name' => '推荐用户']);//推荐用户
        Route::post('spread/order', 'v1.user.UserBillController/spread_order')->name('spreadOrder')->option(['real_name' => '推广订单']);//推广订单
        Route::get('spread/commission/:type', 'v1.user.UserBillController/spread_commission')->name('spreadCommission')->option(['real_name' => '推广佣金明细']);//推广佣金明细
        Route::get('spread/count/:type', 'v1.user.UserBillController/spread_count')->name('spreadCount')->option(['real_name' => '推广佣金']);//推广 佣金 3/提现 4 总和
        Route::get('spread/banner', 'v1.user.UserBillController/spread_banner')->name('spreadBanner')->option(['real_name' => '推广分销二维码海报生成']);//推广分销二维码海报生成
        Route::get('integral/list', 'v1.user.UserBillController/integral_list')->name('integralList')->option(['real_name' => '积分记录']);//积分记录
        Route::get('user/routine_code', 'v1.user.UserBillController/getRoutineCode')->name('getRoutineCode')->option(['real_name' => '小程序二维码']);//小程序二维码
        Route::get('user/spread_info', 'v1.user.UserBillController/getSpreadInfo')->name('getSpreadInfo')->option(['real_name' => '获取分销背景等信息']);//获取分销背景等信息
        Route::post('division/order', 'v1.user.UserBillController/divisionOrder')->name('divisionOrder')->option(['real_name' => '事业部推广订单']);//事业部推广订单
    })->option(['mark' => 'division', 'mark_name' => '账单']);

    Route::group(function () {
        //提现类
        Route::get('extract/bank', 'v1.user.UserExtractController/bank')->name('extractBank')->option(['real_name' => '提现银行']);//提现银行/提现最低金额
        Route::post('extract/cash', 'v1.user.UserExtractController/cash')->name('extractCash')->option(['real_name' => '提现申请']);//提现申请
    })->option(['mark' => 'extract', 'mark_name' => '提现']);

    Route::group(function () {
        //充值类
        Route::post('recharge/recharge', 'v1.user.UserRechargeController/recharge')->name('rechargeRecharge')->option(['real_name' => '统一充值']);//统一充值
        Route::post('recharge/routine', 'v1.user.UserRechargeController/routine')->name('rechargeRoutine')->option(['real_name' => '小程序充值']);//小程序充值
        Route::post('recharge/wechat', 'v1.user.UserRechargeController/wechat')->name('rechargeWechat')->option(['real_name' => '公众号充值']);//公众号充值
        Route::get('recharge/index', 'v1.user.UserRechargeController/index')->name('rechargeQuota')->option(['real_name' => '充值余额选择']);//充值余额选择
    })->option(['mark' => 'recharge', 'mark_name' => '充值']);

    Route::group(function () {
        //会员等级类
        Route::get('user/level/detection', 'v1.user.UserLevelController/detection')->name('userLevelDetection')->option(['real_name' => '检测用户是否可以成为会员']);//检测用户是否可以成为会员
        Route::get('user/level/grade', 'v1.user.UserLevelController/grade')->name('userLevelGrade')->option(['real_name' => '会员等级列表']);//会员等级列表
        Route::get('user/level/task/:id', 'v1.user.UserLevelController/task')->name('userLevelTask')->option(['real_name' => '获取等级任务']);//获取等级任务
        Route::get('user/level/info', 'v1.user.UserLevelController/userLevelInfo')->name('levelInfo')->option(['real_name' => '获取等级任务']);//获取等级任务
        Route::get('user/level/expList', 'v1.user.UserLevelController/expList')->name('expList')->option(['real_name' => '获取等级任务']);//获取等级任务
        Route::get('user/record', 'v1.user.StoreService/recordList')->name('recordList')->option(['real_name' => '获取用户和客服的消息列表']);//获取用户和客服的消息列表
    })->option(['mark' => 'user_level', 'mark_name' => '会员等级']);

    Route::group(function () {
        //会员卡
        Route::get('user/member/card/index', 'v1.user.MemberCardController/index')->name('userMemberCardIndex')->option(['real_name' => '主页会员权益介绍页']);// 主页会员权益介绍页
        Route::post('user/member/card/draw', 'v1.user.MemberCardController/draw_member_card')->name('userMemberCardDraw')->option(['real_name' => '卡密领取会员卡']);//卡密领取会员卡
        Route::post('user/member/card/create', 'v1.order.OtherOrderController/create')->name('userMemberCardCreate')->option(['real_name' => '购买卡创建订单']);//购买卡创建订单
        Route::get('user/member/coupons/list', 'v1.user.MemberCardController/memberCouponList')->name('userMemberCouponsList')->option(['real_name' => '会员券列表']);//会员券列表
        Route::get('user/member/overdue/time', 'v1.user.MemberCardController/getOverdueTime')->name('userMemberOverdueTime')->option(['real_name' => '会员时间']);//会员时间
    })->option(['parent' => 'user', 'cate_name' => '会员卡']);

    Route::group(function () {
        //线下付款
        Route::post('order/offline/check/price', 'v1.order.OtherOrderController/computed_offline_pay_price')->name('orderOfflineCheckPrice')->option(['real_name' => '检测线下付款金额']); //检测线下付款金额
        Route::post('order/offline/create', 'v1.order.OtherOrderController/create')->name('orderOfflineCreate')->option(['real_name' => '检测线下付款金额']); //检测线下付款金额
        Route::get('order/offline/pay/type', 'v1.order.OtherOrderController/pay_type')->name('orderOfflineCreate')->option(['real_name' => '线下付款支付方式']); //线下付款支付方式
    })->option(['mark' => 'offline', 'mark_name' => '线下付款']);

    Route::group(function () {
        //消息站内信
        Route::get('user/message_system/list', 'v1.user.MessageSystemController/message_list')->name('MessageSystemList')->option(['real_name' => '站内信列表']); //站内信列表
        Route::get('user/message_system/detail/:id', 'v1.user.MessageSystemController/detail')->name('MessageSystemDetail')->option(['real_name' => '详情']); //详情
        Route::get('user/message_system/edit_message', 'v1.user.MessageSystemController/edit_message')->name('EditMessage')->option(['real_name' => '站内信设置']);//站内信设为未读/删除
    })->option(['mark' => 'message_system', 'mark_name' => '站内信']);

    Route::group(function () {
        //积分商城订单
        Route::post('store_integral/order/confirm', 'v1.order.StoreIntegralOrderController/confirm')->name('storeIntegralOrderConfirm')->option(['real_name' => '订单确认']); //订单确认
        Route::post('store_integral/order/create', 'v1.order.StoreIntegralOrderController/create')->name('storeIntegralOrderCreate')->option(['real_name' => '订单创建']); //订单创建
        Route::get('store_integral/order/detail/:uni', 'v1.order.StoreIntegralOrderController/detail')->name('storeIntegralOrderDetail')->option(['real_name' => '订单详情']); //订单详情
        Route::get('store_integral/order/list', 'v1.order.StoreIntegralOrderController/lst')->name('storeIntegralOrderList')->option(['real_name' => '订单列表']); //订单列表
        Route::post('store_integral/order/take', 'v1.order.StoreIntegralOrderController/take')->name('storeIntegralOrderTake')->option(['real_name' => '订单收货']); //订单收货
        Route::get('store_integral/order/express/:uni', 'v1.order.StoreIntegralOrderController/express')->name('storeIntegralOrderExpress')->option(['real_name' => '订单查看物流']); //订单查看物流
        Route::post('store_integral/order/del', 'v1.order.StoreIntegralOrderController/del')->name('storeIntegralOrderDel')->option(['real_name' => '订单删除']); //订单删除
    })->option(['mark' => 'order_integral', 'mark_name' => '积分订单']);;

    Route::group(function () {
        /** 退款相关 */
        Route::get('order/refund/cart_info/:id', 'v1.order.StoreOrderController/refundCartInfo')->name('refundCartInfo')->option(['real_name' => '退款中间页面订单商品列表']);//退款中间页面订单商品列表
        Route::post('order/refund/cart_info', 'v1.order.StoreOrderController/refundCartInfoList')->name('StoreOrderRefundCartInfoList')->option(['real_name' => '获取退款商品列表']);//获取退款商品列表
        Route::post('order/refund/apply/:id', 'v1.order.StoreOrderController/applyRefund')->name('StoreOrderApplyRefund')->option(['real_name' => '订单申请退款']);//订单申请退款
        Route::get('order/refund/list', 'v1.order.StoreOrderRefundController/refundList')->name('refundList')->option(['real_name' => '退款单列表']);//退款单列表
        Route::get('order/refund/detail/:uni', 'v1.order.StoreOrderRefundController/refundDetail')->name('refundDetail')->option(['real_name' => '退款单详情']);//退款单详情
        Route::post('order/refund/cancel/:uni', 'v1.order.StoreOrderRefundController/cancelApply')->name('cancelApply')->option(['real_name' => '用户取消退款申请']);//用户取消退款申请
        Route::post('order/refund/express', 'v1.order.StoreOrderRefundController/applyExpress')->name('refundDetail')->option(['real_name' => '退款单详情']);//退款单详情
        Route::get('order/refund/del/:uni', 'v1.order.StoreOrderRefundController/delRefund')->name('delRefund')->option(['real_name' => '用户取消退款申请']);//用户取消退款申请
    })->option(['mark' => 'refund', 'mark_name' => '售后']);

    Route::group(function () {
        /** 代理商相关 */
        Route::get('agent/apply/info', 'v1.user.DivisionController/applyInfo')->name('申请详情')->option(['real_name' => '申请详情']);//申请详情
        Route::post('agent/apply/:id', 'v1.user.DivisionController/applyAgent')->name('applyAgent')->option(['real_name' => '申请代理商']);//申请代理商
        Route::get('agent/get_agent_agreement', 'v1.user.DivisionController/getAgentAgreement')->name('getAgentAgreement')->option(['real_name' => '代理商规则']);//代理商规则
        Route::get('agent/get_staff_list', 'v1.user.DivisionController/getStaffList')->name('getStaffList')->option(['real_name' => '员工列表']);//员工列表
        Route::post('agent/set_staff_percent', 'v1.user.DivisionController/setStaffPercent')->name('setStaffPercent')->option(['real_name' => '设置员工分佣比例']);//设置员工分佣比例
        Route::get('agent/del_staff/:uid', 'v1.user.DivisionController/delStaff')->name('delStaff')->option(['real_name' => '删除员工']);//删除员工
        Route::post('agent/spread', 'v1.user.DivisionController/agentSpread')->name('agentSpread')->option(['real_name' => '代理商绑定员工']);//代理商绑定员工
    })->option(['mark' => 'agent', 'mark_name' => '代理商']);

    Route::group(function () {
        /** 佣金相关 */
        Route::get('commission', 'v1.user.UserBrokerageController/commission')->name('commission')->option(['real_name' => '推广数据']);//推广数据 昨天的佣金 累计提现金额 当前佣金
        Route::get('brokerage_rank', 'v1.user.UserBrokerageController/brokerageRank')->name('brokerageRank')->option(['real_name' => '佣金排行']);//佣金排行
        /** 用户注销 */
        Route::get('user_cancel', 'v1.user.UserController/SetUserCancel')->name('SetUserCancel')->option(['real_name' => '用户注销']);//用户注销
        /** 用户浏览记录 */
        Route::get('user/visit_list', 'v1.user.UserController/visitList')->name('visitList')->option(['real_name' => '商品浏览列表']);//商品浏览列表
        Route::delete('user/visit', 'v1.user.UserController/visitDelete')->name('visitDelete')->option(['real_name' => '商品浏览记录删除']);//商品浏览记录删除
    })->option(['mark' => 'user', 'mark_name' => '用户']);

    Route::group(function () {
        /** 分销员申请 */
        Route::get('user/spread/apply/info', 'v1.user.SpreadApplyController/applyInfo')->name('申请信息');//申请信息
        Route::post('user/spread/apply/:id', 'v1.user.SpreadApplyController/applyPromoter')->name('申请分销员');//申请分销员
    })->option(['mark' => 'spread', 'mark_name' => '分销员申请']);

})->middleware(\app\http\middleware\AllowOriginMiddleware::class)->middleware(\app\api\middleware\StationOpenMiddleware::class)->middleware(\app\api\middleware\AuthTokenMiddleware::class, true);
//未授权接口
Route::group(function () {
    Route::group(function () {
        Route::get('menu/user', 'v1.PublicController/menu_user')->name('menuUser')->option(['real_name' => '个人中心菜单']);//个人中心菜单
        //公共类
        Route::get('index', 'v1.PublicController/index')->name('index')->option(['real_name' => '首页']);//首页
        Route::get('site_config', 'v1.PublicController/getSiteConfig')->name('getSiteConfig')->option(['real_name' => '获取网站配置']);//获取网站配置
        //DIY接口
        Route::get('diy/get_diy/[:id]', 'v1.PublicController/getDiy');
        Route::get('home/products', 'v1.PublicController/home_products_list')->name('homeProductsList')->option(['real_name' => '获取首页推荐不同类型商品的轮播图和商品']);//获取首页推荐不同类型商品的轮播图和商品
    })->option(['mark' => 'index', 'mark_name' => '主页接口']);

    Route::group(function () {
        Route::get('search/keyword', 'v1.PublicController/search')->name('searchKeyword')->option(['real_name' => '热门搜索关键字获取']);//热门搜索关键字获取
        //商品分类类
        Route::get('category', 'v1.store.CategoryController/category')->name('category')->option(['real_name' => '商品分类类']);
        Route::get('category_version', 'v1.store.CategoryController/getCategoryVersion')->name('getCategoryVersion')->option(['real_name' => '商品分类类版本']);//商品分类类版本

        //商品类
        Route::post('image_base64', 'v1.PublicController/get_image_base64')->name('getImageBase64')->option(['real_name' => '获取图片base64']);// 获取图片base64
        Route::get('product/detail/:id/[:type]', 'v1.store.StoreProductController/detail')->name('detail')->option(['real_name' => '商品详情']);//商品详情
        Route::get('groom/list/:type', 'v1.store.StoreProductController/groom_list')->name('groomList')->option(['real_name' => '获取首页推荐不同类型商品的轮播图和商品']);//获取首页推荐不同类型商品的轮播图和商品
        Route::get('products', 'v1.store.StoreProductController/lst')->name('products')->option(['real_name' => '商品列表']);//商品列表
        Route::get('product/hot', 'v1.store.StoreProductController/product_hot')->name('productHot')->option(['real_name' => '为你推荐']);//为你推荐
        Route::get('reply/list/:id', 'v1.store.StoreProductController/reply_list')->name('replyList')->option(['real_name' => '商品评价列表']);//商品评价列表
        Route::get('reply/config/:id', 'v1.store.StoreProductController/reply_config')->name('replyConfig')->option(['real_name' => '商品评价数量和好评度']);//商品评价数量和好评度
        Route::get('advance/list', 'v1.store.StoreProductController/advanceList')->name('advanceList')->option(['real_name' => '预售商品列表']);//预售商品列表
        Route::get('product/code/:id', 'v1.store.StoreProductController/code')->name('productCode')->option(['real_name' => '商品分享二维码']);//商品分享二维码 推广员
        Route::get('product/real_price/:id/:unique', 'v1.store.StoreProductController/realPrice')->name('realPrice')->option(['real_name' => '商品到手价']);//商品到手价
    })->option(['mark' => 'product', 'mark_name' => '商品']);

    Route::group(function () {

        Route::group(function () {
            //文章分类类
            Route::get('article/category/list', 'v1.publics.ArticleCategoryController/lst')->name('articleCategoryList')->option(['real_name' => '文章分类列表']);//文章分类列表
            //文章类
            Route::get('article/list/:cid', 'v1.publics.ArticleController/lst')->name('articleList')->option(['real_name' => '文章列表']);//文章列表
            Route::get('article/details/:id', 'v1.publics.ArticleController/details')->name('articleDetails')->option(['real_name' => '文章详情']);//文章详情
            Route::get('article/hot/list', 'v1.publics.ArticleController/hot')->name('articleHotList')->option(['real_name' => '文章 热门']);//文章 热门
            Route::get('article/new/list', 'v1.publics.ArticleController/new')->name('articleNewList')->option(['real_name' => '文章 最新']);//文章 最新
            Route::get('article/banner/list', 'v1.publics.ArticleController/banner')->name('articleBannerList')->option(['real_name' => '文章 banner']);//文章 banner
        })->option(['parent' => 'activity_nologin', 'cate_name' => '文章(未授权)']);

        Route::group(function () {
            //活动---秒杀
            Route::get('seckill/index', 'v1.activity.StoreSeckillController/index')->name('seckillIndex')->option(['real_name' => '秒杀商品时间区间']);//秒杀商品时间区间
            Route::get('seckill/list/:time', 'v1.activity.StoreSeckillController/lst')->name('seckillList')->option(['real_name' => '秒杀商品列表']);//秒杀商品列表
            Route::get('seckill/detail/:id', 'v1.activity.StoreSeckillController/detail')->name('seckillDetail')->option(['real_name' => '秒杀商品详情']);//秒杀商品详情
        })->option(['parent' => 'activity_nologin', 'cate_name' => '秒杀(未授权)']);

        Route::group(function () {
            //活动---砍价
            Route::get('bargain/config', 'v1.activity.StoreBargainController/config')->name('bargainConfig')->option(['real_name' => '砍价商品列表配置']);//砍价商品列表配置
            Route::get('bargain/list', 'v1.activity.StoreBargainController/lst')->name('bargainList')->option(['real_name' => '砍价商品列表']);//砍价商品列表
        })->option(['parent' => 'activity_nologin', 'cate_name' => '砍价(未授权)']);

        Route::group(function () {
            //活动---拼团
            Route::get('combination/list', 'v1.activity.StoreCombinationController/lst')->name('combinationList')->option(['real_name' => '拼团商品列表']);//拼团商品列表
            Route::get('combination/banner_list', 'v1.activity.StoreCombinationController/banner_list')->name('banner_list')->option(['real_name' => '拼团商品列表']);//拼团商品列表
            Route::get('combination/detail/:id', 'v1.activity.StoreCombinationController/detail')->name('combinationDetail')->option(['real_name' => '拼团商品详情']);//拼团商品详情
        })->option(['parent' => 'activity_nologin', 'cate_name' => '拼团(未授权)']);

        //活动-预售
        Route::get('advance/detail/:id', 'v1.activity.StoreAdvanceController/detail')->name('advanceDetail')->option(['real_name' => '预售商品详情']);//预售商品详情

        //用户类
        Route::get('user/activity', 'v1.user.UserController/activity')->name('userActivity')->option(['real_name' => '活动状态']);//活动状态

    })->option(['mark' => 'activity_nologin', 'mark_name' => '活动']);

    Route::group(function () {
        //微信
        Route::get('wechat/config', 'v1.wechat.WechatController/config')->name('wechatConfig')->option(['real_name' => '微信 sdk 配置']);//微信 sdk 配置
        Route::get('wechat/auth', 'v1.wechat.WechatController/auth')->name('wechatAuth')->option(['real_name' => '微信授权']);//微信授权
        Route::post('wechat/app_auth', 'v1.wechat.WechatController/appAuth')->name('appAuth')->option(['real_name' => '微信APP授权']);//微信APP授权

    })->option(['mark' => 'wechat', 'mark_name' => '微信']);

    Route::group(function () {
        //小程序登陆
        Route::post('wechat/mp_auth', 'v1.wechat.AuthController/mp_auth')->name('mpAuth')->option(['real_name' => '小程序登陆']);//小程序登陆
        Route::get('wechat/get_logo', 'v1.wechat.AuthController/get_logo')->name('getLogo')->option(['real_name' => '小程序登陆授权展示logo']);//小程序登陆授权展示logo
        Route::get('wechat/temp_ids', 'v1.wechat.AuthController/temp_ids')->name('wechatTempIds')->option(['real_name' => '小程序订阅消息']);//小程序订阅消息
        Route::get('wechat/live', 'v1.wechat.AuthController/live')->name('wechatLive')->option(['real_name' => '小程序直播列表']);//小程序直播列表
        Route::get('wechat/livePlaybacks/:id', 'v1.wechat.AuthController/livePlaybacks')->name('livePlaybacks')->option(['real_name' => '小程序直播回放']);//小程序直播回放
    })->option(['mark' => 'mini', 'mark_name' => '小程序']);

    Route::group(function () {
        //物流公司
        Route::get('logistics', 'v1.PublicController/logistics')->name('logistics')->option(['real_name' => '物流公司列表']);//物流公司列表

        //分享配置
        Route::get('share', 'v1.PublicController/share')->name('share')->option(['real_name' => '分享配置']);//分享配置

        //优惠券
        Route::get('coupons', 'v1.store.StoreCouponsController/lst')->name('couponsList')->option(['real_name' => '可领取优惠券列表']); //可领取优惠券列表

        //短信购买异步通知
        Route::post('sms/pay/notify', 'v1.PublicController/sms_pay_notify')->name('smsPayNotify')->option(['real_name' => '短信购买异步通知']); //短信购买异步通知

        //获取关注微信公众号海报
        Route::get('wechat/follow', 'v1.wechat.WechatController/follow')->name('Follow')->option(['real_name' => '获取关注微信公众号海报']);
        //用户是否关注
        Route::get('subscribe', 'v1.user.UserController/subscribe')->name('Subscribe')->option(['real_name' => '用户是否关注']);
        //门店列表
        Route::get('store_list', 'v1.PublicController/store_list')->name('storeList')->option(['real_name' => '门店列表']);
        //获取城市列表
        Route::get('city_list', 'v1.PublicController/city_list')->name('cityList')->option(['real_name' => '获取城市列表']);
        //拼团数据
        Route::get('pink', 'v1.PublicController/pink')->name('pinkData')->option(['real_name' => '拼团数据']);
        //获取底部导航
        Route::get('navigation/[:template_name]', 'v1.PublicController/getNavigation')->name('getNavigation')->option(['real_name' => '获取底部导航']);
        //用户访问
        Route::post('user/set_visit', 'v1.user.UserController/set_visit')->name('setVisit')->option(['real_name' => '添加用户访问记录']);// 添加用户访问记录
        //复制口令接口
        Route::get('copy_words', 'v1.PublicController/copy_words')->name('copyWords')->option(['real_name' => '复制口令接口']);// 复制口令接口
        //获取网站配置
        Route::get('site_config', 'v1.PublicController/getSiteConfig')->name('getSiteConfig')->option(['real_name' => '获取网站配置']);//获取网站配置
    })->option(['mark' => 'setting', 'mark_name' => '商城配置']);

    Route::group(function () {
        //活动---积分商城
        Route::get('store_integral/index', 'v1.activity.StoreIntegralController/index')->name('storeIntegralIndex')->option(['real_name' => '积分商城首页数据']);//积分商城首页数据
        Route::get('store_integral/list', 'v1.activity.StoreIntegralController/lst')->name('storeIntegralList')->option(['real_name' => '积分商品列表']);//积分商品列表
        Route::get('store_integral/detail/:id', 'v1.activity.StoreIntegralController/detail')->name('storeIntegralDetail')->option(['real_name' => '积分商品详情']);//积分商品详情

    })->option(['mark' => 'integral_nologin', 'mark_name' => '积分商城(未授权)']);

    Route::group(function () {
        //获取app最新版本
        Route::get('get_new_app/:platform', 'v1.PublicController/getNewAppVersion')->name('getNewAppVersion')->option(['real_name' => '获取app最新版本']);//获取app最新版本
        //获取客服类型
        Route::get('get_customer_type', 'v1.PublicController/getCustomerType')->name('getCustomerType')->option(['real_name' => '获取客服类型']);//获取客服类型
        //长链接设置
        Route::get('get_workerman_url', 'v1.PublicController/getWorkerManUrl')->name('getWorkerManUrl')->option(['real_name' => '长链接设置']);
        //首页开屏广告
        Route::get('get_open_adv', 'v1.PublicController/getOpenAdv')->name('getOpenAdv')->option(['real_name' => '首页开屏广告']);
        //获取用户协议
        Route::get('user_agreement', 'v1.PublicController/getUserAgreement')->name('getUserAgreement')->option(['real_name' => '获取用户协议']);
        //获取协议
        Route::get('get_agreement/:type', 'v1.PublicController/getAgreement')->name('getAgreement')->option(['real_name' => '获取协议']);

    })->option(['mark' => 'other', 'mark_name' => '其他接口']);

    Route::group(function () {
        //获取多语言类型列表
        Route::get('get_lang_type_list', 'v1.PublicController/getLangTypeList')->name('getLangTypeList')->option(['real_name' => '获取多语言类型列表']);
        //获取当前语言json
        Route::get('get_lang_json', 'v1.PublicController/getLangJson')->name('getLangJson')->option(['real_name' => '获取当前语言json']);
        //获取当前后台设置的默认语言类型
        Route::get('get_default_lang_type', 'v1.PublicController/getDefaultLangType')->name('getLangJson')->option(['real_name' => '获取当前后台设置的默认语言类型']);
        //获取当前后台设置的默认语言类型
        Route::get('lang_version', 'v1.PublicController/getLangVersion')->name('getLangVersion')->option(['real_name' => '获取当前后台设置的默认语言类型']);
    })->option(['mark' => 'lang', 'mark_name' => '多语言']);

    Route::group(function () {
        /** 定时任务接口 */
        //定时任务调用接口
        Route::get('crontab/run', 'v1.CrontabController/crontabRun')->name('crontabRun')->option(['real_name' => '定时任务调用接口']);
        //检测定时任务接口
        Route::get('crontab/check', 'v1.CrontabController/crontabCheck')->name('crontabCheck')->option(['real_name' => '检测定时任务接口']);
        //未支付自动取消订单
        Route::get('crontab/order_cancel', 'v1.CrontabController/orderUnpaidCancel')->name('orderUnpaidCancel')->option(['real_name' => '未支付自动取消订单']);
        //拼团到期订单处理
        Route::get('crontab/pink_expiration', 'v1.CrontabController/pinkExpiration')->name('pinkExpiration')->option(['real_name' => '拼团到期订单处理']);
        //自动解绑上级绑定
        Route::get('crontab/agent_unbind', 'v1.CrontabController/agentUnbind')->name('agentUnbind')->option(['real_name' => '自动解绑上级绑定']);
        //更新直播商品状态
        Route::get('crontab/live_product_status', 'v1.CrontabController/syncGoodStatus')->name('syncGoodStatus')->option(['real_name' => '更新直播商品状态']);
        //更新直播间状态
        Route::get('crontab/live_room_status', 'v1.CrontabController/syncRoomStatus')->name('syncRoomStatus')->option(['real_name' => '更新直播间状态']);
        //自动收货
        Route::get('crontab/take_delivery', 'v1.CrontabController/autoTakeOrder')->name('autoTakeOrder')->option(['real_name' => '自动收货']);
        //查询预售到期商品自动下架
        Route::get('crontab/advance_off', 'v1.CrontabController/downAdvance')->name('downAdvance')->option(['real_name' => '查询预售到期商品自动下架']);
        //自动好评
        Route::get('crontab/product_replay', 'v1.CrontabController/autoComment')->name('autoComment')->option(['real_name' => '自动好评']);
        //清除昨日海报
        Route::get('crontab/clear_poster', 'v1.CrontabController/emptyYesterdayAttachment')->name('emptyYesterdayAttachment')->option(['real_name' => '清除昨日海报']);

    })->option(['mark' => 'crontab', 'mark_name' => '定时任务']);

})->middleware(\app\http\middleware\AllowOriginMiddleware::class)
    ->middleware(\app\api\middleware\StationOpenMiddleware::class)
    ->middleware(\app\api\middleware\AuthTokenMiddleware::class, false);

Route::miss(function () {
    if (app()->request->isOptions()) {
        $header = Config::get('cookie.header');
        unset($header['Access-Control-Allow-Credentials']);
        return Response::create('ok')->code(200)->header($header);
    } else
        return Response::create()->code(404);
});
