<?php

use think\facade\Route;
//账号密码登录
Route::post('login', 'AuthController/login')->name('login')
    ->middleware(\app\http\middleware\AllowOriginMiddleware::class);

//手机号登录
Route::post('login/mobile', 'AuthController/mobile')->name('loginMobile')
    ->middleware(\app\http\middleware\AllowOriginMiddleware::class);

//验证码发送
Route::post('register/verify', 'AuthController/verify')->name('registerVerify')
    ->middleware(\app\http\middleware\AllowOriginMiddleware::class);
//手机号注册
Route::post('register', 'AuthController/register')->name('register')
    ->middleware(\app\http\middleware\AllowOriginMiddleware::class);

//手机号修改密码
Route::post('register/reset', 'AuthController/reset')->name('registerReset')
    ->middleware(\app\http\middleware\AllowOriginMiddleware::class);

Route::any('wechat/serve', 'wechat.WechatController/serve');//公众号服务
Route::any('wechat/notify', 'wechat.WechatController/notify');//公众号支付回调
Route::any('routine/notify', 'wechat.AuthController/notify');//小程序支付回调

//管理员订单操作类
Route::group(function () {
    Route::get('admin/order/statistics', 'admin.StoreOrderController/statistics')->name('adminOrderStatistics');//订单数据统计
    Route::get('admin/order/data', 'admin.StoreOrderController/data')->name('adminOrderData');//订单每月统计数据
    Route::get('admin/order/list', 'admin.StoreOrderController/lst')->name('adminOrderList');//订单列表
    Route::get('admin/order/detail/:orderId', 'admin.StoreOrderController/detail')->name('adminOrderDetail');//订单详情
    Route::get('admin/order/delivery/gain/:orderId', 'admin.StoreOrderController/delivery_gain')->name('adminOrderDeliveryGain');//订单发货获取订单信息
    Route::post('admin/order/delivery/keep', 'admin.StoreOrderController/delivery_keep')->name('adminOrderDeliveryKeep');//订单发货
    Route::post('admin/order/price', 'admin.StoreOrderController/price')->name('adminOrderPrice');//订单改价
    Route::post('admin/order/remark', 'admin.StoreOrderController/remark')->name('adminOrderRemark');//订单备注
    Route::get('admin/order/time', 'admin.StoreOrderController/time')->name('adminOrderTime');//订单交易额时间统计
    Route::post('admin/order/offline', 'admin.StoreOrderController/offline')->name('adminOrderOffline');//订单支付
    Route::post('admin/order/refund', 'admin.StoreOrderController/refund')->name('adminOrderRefund');//订单退款
})->middleware(\app\http\middleware\AllowOriginMiddleware::class)->middleware(\app\http\middleware\AuthTokenMiddleware::class, true)->middleware(\app\http\middleware\CustomerMiddleware::class);

//会员授权接口
Route::group(function () {
    Route::get('logout', 'AuthController/logout')->name('logout');// 退出登录
    Route::post('switch_h5', 'AuthController/switch_h5')->name('switch_h5');// 切换账号
    Route::post('binding', 'AuthController/binding_phone')->name('bindingPhone');// 绑定手机号
    //产品类
    Route::get('product/code/:id', 'store.StoreProductController/code')->name('productCode');//产品分享二维码 推广员

     //公共类
    Route::post('upload/image', 'PublicController/upload_image')->name('uploadImage');//图片上传
    //用户类 客服聊天记录
    Route::get('user/service/list', 'user.StoreService/lst')->name('userServiceList');//客服列表
    Route::get('user/service/record/:toUid', 'user.StoreService/record')->name('userServiceRecord');//客服聊天记录

    //用户类  用户coupons/order
    Route::get('user', 'user.UserController/user')->name('user');//个人中心
    Route::post('user/edit', 'user.UserController/edit')->name('userEdit');//用户修改信息
    Route::get('user/balance', 'user.UserController/balance')->name('userBalance');//用户资金统计
    Route::get('userinfo', 'user.UserController/userinfo')->name('userinfo');// 用户信息
    //用户类  地址
    Route::get('address/detail/:id', 'user.UserController/address')->name('address');//获取单个地址
    Route::get('address/list', 'user.UserController/address_list')->name('addressList');//地址列表
    Route::post('address/default/set', 'user.UserController/address_default_set')->name('addressDefaultSet');//设置默认地址
    Route::get('address/default', 'user.UserController/address_default')->name('addressDefault');//获取默认地址
    Route::post('address/edit', 'user.UserController/address_edit')->name('addressEdit');//修改 添加 地址
    Route::post('address/del', 'user.UserController/address_del')->name('addressDel');//删除地址
    //用户类 收藏
    Route::get('collect/user', 'user.UserController/collect_user')->name('collectUser');//收藏产品列表
    Route::post('collect/add', 'user.UserController/collect_add')->name('collectAdd');//添加收藏
    Route::post('collect/del', 'user.UserController/collect_del')->name('collectDel');//取消收藏
    Route::post('collect/all', 'user.UserController/collect_all')->name('collectAll');//批量添加收藏
    //用戶类 分享
    Route::post('user/share', 'PublicController/user_share')->name('user_share');//记录用户分享
    //用户类 点赞
//    Route::post('like/add', 'user.UserController/like_add')->name('likeAdd');//添加点赞
//    Route::post('like/del', 'user.UserController/like_del')->name('likeDel');//取消点赞
    //用户类 签到
    Route::get('sign/config', 'user.UserController/sign_config')->name('signConfig');//签到配置
    Route::get('sign/list', 'user.UserController/sign_list')->name('signList');//签到列表
    Route::get('sign/month', 'user.UserController/sign_month')->name('signIntegral');//签到列表（年月）
    Route::post('sign/user', 'user.UserController/sign_user')->name('signUser');//签到用户信息
    Route::post('sign/integral', 'user.UserController/sign_integral')->name('signIntegral');//签到
    //优惠券类
    Route::post('coupon/receive', 'store.StoreCouponsController/receive')->name('couponReceive'); //领取优惠券
    Route::post('coupon/receive/batch', 'store.StoreCouponsController/receive_batch')->name('couponReceiveBatch'); //批量领取优惠券
    Route::get('coupons/user/:types', 'store.StoreCouponsController/user')->name('couponsUser');//用户已领取优惠券
    Route::get('coupons/order/:price', 'store.StoreCouponsController/order')->name('couponsOrder');//优惠券 订单列表
    //购物车类
    Route::get('cart/list', 'store.StoreCartController/lst')->name('cartList'); //购物车列表
    Route::post('cart/add', 'store.StoreCartController/add')->name('cartAdd'); //购物车添加
    Route::post('cart/del', 'store.StoreCartController/del')->name('cartDel'); //购物车删除
    Route::post('order/cancel', 'order.StoreOrderController/cancel')->name('orderCancel'); //订单取消
    Route::post('cart/num', 'store.StoreCartController/num')->name('cartNum'); //购物车 修改产品数量
    Route::get('cart/count', 'store.StoreCartController/count')->name('cartCount'); //购物车 获取数量
    //订单类
    Route::post('order/confirm', 'order.StoreOrderController/confirm')->name('orderConfirm'); //订单确认
    Route::post('order/computed/:key', 'order.StoreOrderController/computedOrder')->name('computedOrder'); //计算订单金额
    Route::post('order/create/:key', 'order.StoreOrderController/create')->name('orderCreate'); //订单创建
    Route::get('order/data', 'order.StoreOrderController/data')->name('orderData'); //订单统计数据
    Route::get('order/list', 'order.StoreOrderController/lst')->name('orderList'); //订单列表
    Route::get('order/detail/:uni', 'order.StoreOrderController/detail')->name('orderDetail'); //订单详情
    Route::get('order/refund/reason', 'order.StoreOrderController/refund_reason')->name('orderRefundReason'); //订单退款理由
    Route::post('order/refund/verify', 'order.StoreOrderController/refund_verify')->name('orderRefundVerify'); //订单退款审核
    Route::post('order/take', 'order.StoreOrderController/take')->name('orderTake'); //订单收货
    Route::get('order/express/:uni', 'order.StoreOrderController/express')->name('orderExpress'); //订单查看物流
    Route::post('order/del', 'order.StoreOrderController/del')->name('orderDel'); //订单删除
    Route::post('order/again', 'order.StoreOrderController/again')->name('orderAgain'); //订单 再次下单
    Route::post('order/pay', 'order.StoreOrderController/pay')->name('orderPay'); //订单支付
    Route::post('order/product', 'order.StoreOrderController/product')->name('orderProduct'); //订单产品信息
    Route::post('order/comment', 'order.StoreOrderController/comment')->name('orderComment'); //订单评价
    //活动---砍价
    Route::get('bargain/detail/:id', 'activity.StoreBargainController/detail')->name('bargainDetail');//砍价产品详情
    Route::post('bargain/start', 'activity.StoreBargainController/start')->name('bargainStart');//砍价开启
    Route::post('bargain/start/user', 'activity.StoreBargainController/start_user')->name('bargainStartUser');//砍价 开启砍价用户信息
    Route::post('bargain/share', 'activity.StoreBargainController/share')->name('bargainShare');//砍价 观看/分享/参与次数
    Route::post('bargain/help', 'activity.StoreBargainController/help')->name('bargainHelp');//砍价 帮助好友砍价
    Route::post('bargain/help/price', 'activity.StoreBargainController/help_price')->name('bargainHelpPrice');//砍价 砍掉金额
    Route::post('bargain/help/count', 'activity.StoreBargainController/help_count')->name('bargainHelpCount');//砍价 砍价帮总人数、剩余金额、进度条、已经砍掉的价格
    Route::post('bargain/help/list', 'activity.StoreBargainController/help_list')->name('bargainHelpList');//砍价 砍价帮
    Route::post('bargain/poster', 'activity.StoreBargainController/poster')->name('bargainPoster');//砍价海报
    Route::get('bargain/user/list', 'activity.StoreBargainController/user_list')->name('bargainUserList');//砍价列表(已参与)
    Route::post('bargain/user/cancel', 'activity.StoreBargainController/user_cancel')->name('bargainUserCancel');//砍价取消
    //活动---拼团
    Route::get('combination/pink/:id', 'activity.StoreCombinationController/pink')->name('combinationPink');//拼团开团
    Route::post('combination/remove', 'activity.StoreCombinationController/remove')->name('combinationRemove');//拼团 取消开团
    Route::post('combination/poster', 'activity.StoreCombinationController/poster')->name('combinationPoster');//拼团海报
    //账单类
    Route::get('commission', 'user.UserBillController/commission')->name('commission');//推广数据 昨天的佣金 累计提现金额 当前佣金
    Route::post('spread/people', 'user.UserBillController/spread_people')->name('spreadPeople');//推荐用户
    Route::post('spread/order', 'user.UserBillController/spread_order')->name('spreadOrder');//推广订单
    Route::get('spread/commission/:type', 'user.UserBillController/spread_commission')->name('spreadCommission');//推广佣金明细
    Route::get('spread/count/:type', 'user.UserBillController/spread_count')->name('spreadCount');//推广 佣金 3/提现 4 总和
    Route::get('spread/banner', 'user.UserBillController/spread_banner')->name('spreadBanner');//推广分销二维码海报生成
    Route::get('integral/list', 'user.UserBillController/integral_list')->name('integralList');//积分记录
    //提现类
    Route::get('extract/bank', 'user.UserExtractController/bank')->name('extractBank');//提现银行/提现最低金额
    Route::post('extract/cash', 'user.UserExtractController/cash')->name('extractCash');//提现申请
    //充值类
    Route::post('recharge/routine', 'user.UserRechargeController/routine')->name('rechargeRoutine');//小程序充值
    Route::post('recharge/wechat', 'user.UserRechargeController/wechat')->name('rechargeWechat');//公众号充值
    //会员等级类
    Route::get('menu/user', 'PublicController/menu_user')->name('menuUser');//个人中心菜单
    Route::get('user/level/detection', 'user.UserLevelController/detection')->name('userLevelDetection');//检测用户是否可以成为会员
    Route::get('user/level/grade', 'user.UserLevelController/grade')->name('userLevelGrade');//会员等级列表
    Route::get('user/level/task/:id', 'user.UserLevelController/task')->name('userLevelTask');//获取等级任务
})->middleware(\app\http\middleware\AllowOriginMiddleware::class)->middleware(\app\http\middleware\AuthTokenMiddleware::class, true);
//未授权接口
Route::group(function () {
    //公共类
    Route::get('index', 'PublicController/index')->name('index');//首页
    Route::get('search/keyword', 'PublicController/search')->name('searchKeyword');//热门搜索关键字获取
    //产品分类类
    Route::get('category', 'store.CategoryController/category')->name('category');
    //产品类
    Route::post('image_base64', 'PublicController/get_image_base64')->name('getImageBase64');// 获取图片base64
    Route::get('product/detail/:id', 'store.StoreProductController/detail')->name('detail');//产品详情
    Route::get('groom/list/:type', 'store.StoreProductController/groom_list')->name('groomList');//获取首页推荐不同类型产品的轮播图和产品
    Route::get('products', 'store.StoreProductController/lst')->name('products');//产品列表
    Route::get('product/hot', 'store.StoreProductController/product_hot')->name('productHot');//为你推荐
    Route::get('reply/list/:id', 'store.StoreProductController/reply_list')->name('replyList');//产品评价列表
    Route::get('reply/config/:id', 'store.StoreProductController/reply_config')->name('replyConfig');//产品评价数量和好评度
    //文章分类类
    Route::get('article/category/list', 'publics.ArticleCategoryController/lst')->name('articleCategoryList');//文章分类列表
    //文章类
    Route::get('article/list/:cid', 'publics.ArticleController/lst')->name('articleList');//文章列表
    Route::get('article/details/:id', 'publics.ArticleController/details')->name('articleDetails');//文章详情
    Route::get('article/hot/list', 'publics.ArticleController/hot')->name('articleHotList');//文章 热门
    Route::get('article/banner/list', 'publics.ArticleController/banner')->name('articleBannerList');//文章 banner
    //活动---秒杀
    Route::get('seckill/index', 'activity.StoreSeckillController/index')->name('seckillIndex');//秒杀产品时间区间
    Route::get('seckill/list/:time', 'activity.StoreSeckillController/lst')->name('seckillList');//秒杀产品列表
    Route::get('seckill/detail/:id/[:time]', 'activity.StoreSeckillController/detail')->name('seckillDetail');//秒杀产品详情
    //活动---砍价
    Route::get('bargain/config', 'activity.StoreBargainController/config')->name('bargainConfig');//砍价产品列表配置
    Route::get('bargain/list', 'activity.StoreBargainController/lst')->name('bargainList');//砍价产品列表
    //活动---拼团
    Route::get('combination/list', 'activity.StoreCombinationController/lst')->name('combinationList');//拼团产品列表
    Route::get('combination/detail/:id', 'activity.StoreCombinationController/detail')->name('combinationDetail');//拼团产品详情
    //用户类
    Route::get('user/activity', 'user.UserController/activity')->name('userActivity');//活动状态

    //微信
    Route::get('wechat/config', 'wechat.WechatController/config')->name('wechatConfig');//微信 sdk 配置
    Route::get('wechat/auth', 'wechat.WechatController/auth')->name('wechatAuth');//微信授权

    //小程序登陆
    Route::post('wechat/mp_auth', 'wechat.AuthController/mp_auth')->name('mpAuth');//小程序登陆
    Route::get('wechat/get_logo', 'wechat.AuthController/get_logo')->name('getLogo');//小程序登陆授权展示logo
    Route::post('wechat/set_form_id', 'wechat.AuthController/set_form_id')->name('setFormId');//小程序登陆收集form id

    //物流公司
    Route::get('logistics', 'PublicController/logistics')->name('logistics');//物流公司列表

    //分享配置
    Route::get('share', 'PublicController/share')->name('share');//分享配置

    //优惠券
    Route::get('coupons', 'store.StoreCouponsController/lst')->name('couponsList'); //可领取优惠券列表

    //短信购买异步通知
    Route::post('sms/pay/notify', 'PublicController/sms_pay_notify')->name('smsPayNotify'); //短信购买异步通知



})->middleware(\app\http\middleware\AllowOriginMiddleware::class)->middleware(\app\http\middleware\AuthTokenMiddleware::class, false);


Route::get('test', 'AuthController/test');
Route::miss(function() {
    return \think\Response::create()->code(404);
});