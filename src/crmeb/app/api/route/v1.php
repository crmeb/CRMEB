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
use think\facade\Config;
use think\Response;

Route::any('wechat/serve', 'v1.wechat.WechatController/serve');//公众号服务
Route::any('wechat/notify', 'v1.wechat.WechatController/notify');//公众号支付回调
Route::any('routine/notify', 'v1.wechat.AuthController/notify');//小程序支付回调

Route::any('pay/notify/:type', 'v1.PayController/notify');//支付回调

Route::group(function () {
    //账号密码登录
    Route::post('login', 'v1.LoginController/login')->name('login');
    // 获取发短信的key
    Route::get('verify_code', 'v1.LoginController/verifyCode')->name('verifyCode');
    //手机号登录
    Route::post('login/mobile', 'v1.LoginController/mobile')->name('loginMobile');
    //图片验证码
    Route::get('sms_captcha', 'v1.LoginController/captcha')->name('captcha');
    //验证码发送
    Route::post('register/verify', 'v1.LoginController/verify')->name('registerVerify');
    //手机号注册
    Route::post('register', 'v1.LoginController/register')->name('register');
    //手机号修改密码
    Route::post('register/reset', 'v1.LoginController/reset')->name('registerReset');
    // 绑定手机号(静默授权 还未有用户信息)
    Route::post('binding', 'v1.LoginController/binding_phone')->name('bindingPhone');

})->middleware(\app\http\middleware\AllowOriginMiddleware::class);

//会员授权接口
Route::group(function () {

    //用户修改手机号
    Route::post('user/updatePhone', 'v1.LoginController/update_binding_phone')->name('updateBindingPhone');
    //设置登录code
    Route::post('user/code', 'v1.user.StoreService/setLoginCode')->name('setLoginCode');
    //查看code是否可用
    Route::get('user/code', 'v1.LoginController/setLoginKey')->name('getLoginKey');
    //用户绑定手机号
    Route::post('user/binding', 'v1.LoginController/user_binding_phone')->name('userBindingPhone');
    Route::get('logout', 'v1.LoginController/logout')->name('logout');// 退出登录
    Route::post('switch_h5', 'v1.LoginController/switch_h5')->name('switch_h5');// 切换账号
    //商品类
    Route::get('product/code/:id', 'v1.store.StoreProductController/code')->name('productCode');//商品分享二维码 推广员

    //公共类
    Route::post('upload/image', 'v1.PublicController/upload_image')->name('uploadImage');//图片上传
    //用户类 客服聊天记录
    Route::get('user/service/list', 'v1.user.StoreService/lst')->name('userServiceList');//客服列表
    Route::get('user/service/record', 'v1.user.StoreService/record')->name('userServiceRecord');//客服聊天记录
    Route::post('user/service/feedback', 'v1.user.StoreService/saveFeedback')->name('saveFeedback');//保存客服反馈信息
    Route::get('user/service/feedback', 'v1.user.StoreService/getFeedbackInfo')->name('getFeedbackInfo');//获得客服反馈头部信息
    Route::get('user/service/get_adv', 'v1.user.StoreService/getKfAdv')->name('userServiceGetKfAdv');//获取客服页面广告

    //用户类  用户coupons/order
    Route::get('user', 'v1.user.UserController/user')->name('user');//个人中心
    Route::post('user/spread', 'v1.user.UserController/spread')->name('userSpread');//静默绑定授权
    Route::post('user/edit', 'v1.user.UserController/edit')->name('userEdit');//用户修改信息
    Route::get('user/balance', 'v1.user.UserController/balance')->name('userBalance');//用户资金统计
    Route::get('userinfo', 'v1.user.UserController/userinfo')->name('userinfo');// 用户信息
    //用户类  地址
    Route::get('address/detail/:id', 'v1.user.UserAddressController/address')->name('address');//获取单个地址
    Route::get('address/list', 'v1.user.UserAddressController/address_list')->name('addressList');//地址列表
    Route::post('address/default/set', 'v1.user.UserAddressController/address_default_set')->name('addressDefaultSet');//设置默认地址
    Route::get('address/default', 'v1.user.UserAddressController/address_default')->name('addressDefault');//获取默认地址
    Route::post('address/edit', 'v1.user.UserAddressController/address_edit')->name('addressEdit');//修改 添加 地址
    Route::post('address/del', 'v1.user.UserAddressController/address_del')->name('addressDel');//删除地址
    //用户类 收藏
    Route::get('collect/user', 'v1.user.UserCollectController/collect_user')->name('collectUser');//收藏商品列表
    Route::post('collect/add', 'v1.user.UserCollectController/collect_add')->name('collectAdd');//添加收藏
    Route::post('collect/del', 'v1.user.UserCollectController/collect_del')->name('collectDel');//取消收藏
    Route::post('collect/all', 'v1.user.UserCollectController/collect_all')->name('collectAll');//批量添加收藏

    //用戶类 分享
    Route::post('user/share', 'v1.PublicController/user_share')->name('user_share');//记录用户分享
    Route::get('user/share/words', 'v1.PublicController/copy_share_words')->name('user_share_words');//关键字分享

    Route::post('sign/user', 'v1.user.UserBillController/point_user')->name('signUser');//用户积分统计
    //优惠券类
    Route::post('coupon/receive', 'v1.store.StoreCouponsController/receive')->name('couponReceive'); //领取优惠券
    Route::post('coupon/receive/batch', 'v1.store.StoreCouponsController/receive_batch')->name('couponReceiveBatch'); //批量领取优惠券
    Route::get('coupons/user/:types', 'v1.store.StoreCouponsController/user')->name('couponsUser');//用户已领取优惠券
    Route::get('coupons/order/:price', 'v1.store.StoreCouponsController/order')->name('couponsOrder');//优惠券 订单列表
    //购物车类
    Route::get('cart/list', 'v1.store.StoreCartController/lst')->name('cartList'); //购物车列表
    Route::post('cart/add', 'v1.store.StoreCartController/add')->name('cartAdd'); //购物车添加
    Route::post('cart/del', 'v1.store.StoreCartController/del')->name('cartDel'); //购物车删除
    Route::post('order/cancel', 'v1.order.StoreOrderController/cancel')->name('orderCancel'); //订单取消
    Route::post('cart/num', 'v1.store.StoreCartController/num')->name('cartNum'); //购物车 修改商品数量
    Route::get('cart/count', 'v1.store.StoreCartController/count')->name('cartCount'); //购物车 获取数量
    //订单类
    Route::post('order/confirm', 'v1.order.StoreOrderController/confirm')->name('orderConfirm'); //订单确认
    Route::post('order/computed/:key', 'v1.order.StoreOrderController/computedOrder')->name('computedOrder'); //计算订单金额
    Route::post('order/create/:key', 'v1.order.StoreOrderController/create')->name('orderCreate'); //订单创建
    Route::get('order/data', 'v1.order.StoreOrderController/data')->name('orderData'); //订单统计数据
    Route::get('order/list', 'v1.order.StoreOrderController/lst')->name('orderList'); //订单列表
    Route::get('order/detail/:uni', 'v1.order.StoreOrderController/detail')->name('orderDetail'); //订单详情
    Route::get('order/refund/reason', 'v1.order.StoreOrderController/refund_reason')->name('orderRefundReason'); //订单退款理由
    Route::post('order/refund/verify', 'v1.order.StoreOrderController/refund_verify')->name('orderRefundVerify'); //订单退款审核
    Route::post('order/take', 'v1.order.StoreOrderController/take')->name('orderTake'); //订单收货
    Route::get('order/express/:uni', 'v1.order.StoreOrderController/express')->name('orderExpress'); //订单查看物流
    Route::post('order/del', 'v1.order.StoreOrderController/del')->name('orderDel'); //订单删除
    Route::post('order/again', 'v1.order.StoreOrderController/again')->name('orderAgain'); //订单 再次下单
    Route::post('order/pay', 'v1.order.StoreOrderController/pay')->name('orderPay'); //订单支付
    Route::post('order/product', 'v1.order.StoreOrderController/product')->name('orderProduct'); //订单商品信息
    Route::post('order/comment', 'v1.order.StoreOrderController/comment')->name('orderComment'); //订单评价

    Route::get('spread/commission/:type', 'v1.user.UserBillController/spread_commission')->name('spreadCommission');//资金明细
    Route::get('spread/count/:type', 'v1.user.UserBillController/spread_count')->name('spreadCount');//资金总数
    Route::get('integral/list', 'v1.user.UserBillController/integral_list')->name('integralList');//积分记录

    //充值类
    Route::post('recharge/recharge', 'v1.user.UserRechargeController/recharge')->name('rechargeRecharge');//统一充值
    Route::post('recharge/routine', 'v1.user.UserRechargeController/routine')->name('rechargeRoutine');//小程序充值
    Route::post('recharge/wechat', 'v1.user.UserRechargeController/wechat')->name('rechargeWechat');//公众号充值
    Route::get('recharge/index', 'v1.user.UserRechargeController/index')->name('rechargeQuota');//充值余额选择

    //首页获取未支付订单
    Route::get('order/nopay', 'v1.order.StoreOrderController/get_noPay')->name('getNoPay');//获取未支付订单

})->middleware(\app\http\middleware\AllowOriginMiddleware::class)->middleware(\app\api\middleware\AuthTokenMiddleware::class, true);
//未授权接口
Route::group(function () {
    Route::get('menu/user', 'v1.PublicController/menu_user')->name('menuUser');//个人中心菜单
    //公共类
    Route::get('index', 'v1.PublicController/index')->name('index');//首页
    Route::get('search/keyword', 'v1.PublicController/search')->name('searchKeyword');//热门搜索关键字获取
    //商品分类类
    Route::get('category', 'v1.store.CategoryController/category')->name('category');
    //商品类
    Route::post('image_base64', 'v1.PublicController/get_image_base64')->name('getImageBase64');// 获取图片base64
    Route::get('product/detail/:id/[:type]', 'v1.store.StoreProductController/detail')->name('detail');//商品详情
    Route::get('groom/list/:type', 'v1.store.StoreProductController/groom_list')->name('groomList');//获取首页推荐不同类型商品的轮播图和商品
    Route::get('products', 'v1.store.StoreProductController/lst')->name('products');//商品列表
    Route::get('product/hot', 'v1.store.StoreProductController/product_hot')->name('products');//商品列表

    Route::get('reply/list/:id', 'v1.store.StoreProductController/reply_list')->name('replyList');//商品评价列表
    Route::get('reply/config/:id', 'v1.store.StoreProductController/reply_config')->name('replyConfig');//商品评价数量和好评度
    //文章分类类
    Route::get('article/category/list', 'v1.publics.ArticleCategoryController/lst')->name('articleCategoryList');//文章分类列表
    //文章类
    Route::get('article/list/:cid', 'v1.publics.ArticleController/lst')->name('articleList');//文章列表
    Route::get('article/details/:id', 'v1.publics.ArticleController/details')->name('articleDetails');//文章详情
    Route::get('article/hot/list', 'v1.publics.ArticleController/hot')->name('articleHotList');//文章 热门
    Route::get('article/new/list', 'v1.publics.ArticleController/new')->name('articleNewList');//文章 最新
    Route::get('article/banner/list', 'v1.publics.ArticleController/banner')->name('articleBannerList');//文章 banner

    //微信
    Route::get('wechat/config', 'v1.wechat.WechatController/config')->name('wechatConfig');//微信 sdk 配置
    Route::get('wechat/auth', 'v1.wechat.WechatController/auth')->name('wechatAuth');//微信授权

    //小程序登陆
    Route::post('wechat/mp_auth', 'v1.wechat.AuthController/mp_auth')->name('mpAuth');//小程序登陆
    Route::get('wechat/get_logo', 'v1.wechat.AuthController/get_logo')->name('getLogo');//小程序登陆授权展示logo
    Route::get('wechat/teml_ids', 'v1.wechat.AuthController/teml_ids')->name('wechatTemlIds');//小程序订阅消息

    //物流公司
    Route::get('logistics', 'v1.PublicController/logistics')->name('logistics');//物流公司列表
    //分享配置
    Route::get('share', 'v1.PublicController/share')->name('share');//分享配置
    //优惠券
    Route::get('coupons', 'v1.store.StoreCouponsController/lst')->name('couponsList'); //可领取优惠券列表
    //短信购买异步通知
    Route::post('sms/pay/notify', 'v1.PublicController/sms_pay_notify')->name('smsPayNotify'); //短信购买异步通知
    //获取关注微信公众号海报
    Route::get('wechat/follow', 'v1.wechat.WechatController/follow')->name('Follow');
    //获取城市列表
    Route::get('city_list', 'v1.PublicController/city_list')->name('cityList');
    //用户访问
    Route::post('user/set_visit', 'v1.user.UserController/set_visit')->name('setVisit');// 添加用户访问记录

})->middleware(\app\http\middleware\AllowOriginMiddleware::class)->middleware(\app\api\middleware\AuthTokenMiddleware::class, false);

Route::miss(function () {
    if (app()->request->isOptions()) {
        $header = Config::get('cookie.header');
        unset($header['Access-Control-Allow-Credentials']);
        return Response::create('ok')->code(200)->header($header);
    } else
        return Response::create()->code(404);
});
