<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
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
    Route::any('pay/notify/:type', [\app\api\controller\v1\PayController::class, 'notify'])->option(['real_name' => '支付回调']);//支付回调
    Route::any('transfer/notify/:type', [\app\api\controller\v1\PayController::class, 'transferNotify'])->option(['real_name' => '商户转账回调']);//商户转账回调
    Route::any('order_call_back', [\app\api\controller\v1\order\CenterOrderController::class, 'callBack'])->option(['real_name' => '商家寄件回调']);//商家寄件回调
    Route::get('get_script', [\app\api\controller\v1\PublicController::class, 'getScript'])->option(['real_name' => '移动端自定义JS']);//移动端自定义JS
    Route::get('version', [\app\api\controller\v1\PublicController::class, 'getVersion'])->option(['real_name' => '获取代码版本号']);
    Route::get('service_pay_result', [\app\api\controller\v1\PublicController::class, 'servicePayResult'])->option(['real_name' => '服务商支付商家小票接口']);
})->middleware(\app\http\middleware\AllowOriginMiddleware::class)->option(['mark' => 'serve', 'mark_name' => '服务接口']);

Route::group(function () {
    //apple快捷登陆
    Route::post('apple_login', [\app\api\controller\v1\LoginController::class, 'appleLogin'])->name('appleLogin')->option(['real_name' => '微信APP授权']);//微信APP授权
    //账号密码登录
    Route::post('login', [\app\api\controller\v1\LoginController::class, 'login'])->name('login')->option(['real_name' => '账号密码登录']);
    // 获取发短信的key
    Route::get('verify_code', [\app\api\controller\v1\LoginController::class, 'verifyCode'])->name('verifyCode')->option(['real_name' => '获取发短信的key']);
    //手机号登录
    Route::post('login/mobile', [\app\api\controller\v1\LoginController::class, 'mobile'])->name('loginMobile')->option(['real_name' => '手机号登录']);
    //图片验证码
    Route::get('sms_captcha', [\app\api\controller\v1\LoginController::class, 'captcha'])->name('captcha')->option(['real_name' => '图片验证码']);
    //图形验证码
    Route::get('ajcaptcha', [\app\api\controller\v1\LoginController::class, 'ajcaptcha'])->name('ajcaptcha')->option(['real_name' => '图形验证码']);
    //图形验证码验证
    Route::post('ajcheck', [\app\api\controller\v1\LoginController::class, 'ajcheck'])->name('ajcheck')->option(['real_name' => '图形验证码验证']);
    //手机验证码发送
    Route::post('register/verify', [\app\api\controller\v1\LoginController::class, 'verify'])->name('registerVerify')->option(['real_name' => '手机验证码发送']);
    //手机号注册
    Route::post('register', [\app\api\controller\v1\LoginController::class, 'register'])->name('register')->option(['real_name' => '手机号注册']);
    //手机号修改密码
    Route::post('register/reset', [\app\api\controller\v1\LoginController::class, 'reset'])->name('registerReset')->option(['real_name' => '手机号修改密码']);
    // 绑定手机号(静默授权 还未有用户信息)
    Route::post('binding', [\app\api\controller\v1\LoginController::class, 'bindingPhone'])->name('bindingPhone')->option(['real_name' => '绑定手机号']);
    // 支付宝复制链接支付 弃用
//    Route::get('ali_pay', 'v1.order.StoreOrderController/aliPay')->name('aliPay');
    //查询版权
    Route::get('copyright', [\app\api\controller\v1\PublicController::class, 'copyright'])->option(['real_name' => '申请版权'])->option(['real_name' => '查询版权']);
    //商城基础配置汇总接口
    Route::get('basic_config', [\app\api\controller\v1\PublicController::class, 'getMallBasicConfig'])->option(['real_name' => '商城基础配置汇总接口']);
    //小程序跳转url接口
    Route::get('get_scheme_url/:id', [\app\api\controller\v1\PublicController::class, 'getSchemeUrl'])->option(['real_name' => '小程序跳转url接口']);
    //远程注册用户
    Route::get('remote_register', [\app\api\controller\v1\LoginController::class, 'remoteRegister'])->option(['real_name' => '远程注册用户']);

    // ============ 统一认证接口（跨渠道统一登录/注册入口）============
    Route::post('auth/login', [\app\api\controller\v1\AuthController::class, 'login'])->name('authLogin')->option(['real_name' => '统一登录']);
    Route::post('auth/register', [\app\api\controller\v1\AuthController::class, 'register'])->name('authRegister')->option(['real_name' => '统一注册']);

})->middleware(\app\http\middleware\AllowOriginMiddleware::class)
    ->middleware(\app\api\middleware\StationOpenMiddleware::class)
    ->option(['mark' => 'base', 'mark_name' => '基础接口']);


//会员授权接口
Route::group(function () {
    Route::group(function () {
        //获取支付方式
        Route::get('pay/config', [\app\api\controller\v1\PayController::class, 'config'])->name('payConfig')->option(['real_name' => '获取支付方式']);
        //用户修改手机号
        Route::post('user/updatePhone', [\app\api\controller\v1\LoginController::class, 'updateBindingPhone'])->name('updateBindingPhone')->option(['real_name' => '用户修改手机号']);
        //查看code是否可用
        Route::get('user/code', [\app\api\controller\v1\LoginController::class, 'setLoginKey'])->name('getLoginKey')->option(['real_name' => '查看code是否可用']);
        //用户绑定手机号
        Route::post('user/binding', [\app\api\controller\v1\LoginController::class, 'userBindingPhone'])->name('userBindingPhone')->option(['real_name' => '用户绑定手机号']);
        Route::get('logout', [\app\api\controller\v1\LoginController::class, 'logout'])->name('logout')->option(['real_name' => '退出登录']);// 退出登录
        // 统一退出登录（触发 UserLogout 事件）
        Route::get('auth/logout', [\app\api\controller\v1\AuthController::class, 'logout'])->name('authLogout')->option(['real_name' => '统一退出登录']);
        Route::post('switch_h5', [\app\api\controller\v1\LoginController::class, 'switchH5'])->name('switch_h5')->option(['real_name' => '切换账号']);// 切换账号
        //公共类
        Route::post('upload/image', [\app\api\controller\v1\PublicController::class, 'uploadImage'])->name('uploadImage')->option(['real_name' => '图片上传']);//图片上传
        // 用户微信转账详情接口
        Route::get('transfer/info', [\app\api\controller\v1\PublicController::class, 'getTransferInfo'])->name('getTransferInfo')->option(['real_name' => '用户微信转账详情接口']);// 用户微信转账详情接口
        Route::post('transfer/info', [\app\api\controller\v1\user\UserExtractController::class, 'checkTransfer'])->name('checkTransfer')->option(['real_name' => '用户微信转账收款核验']);// 核验并同步当前用户的转账终态

    })->option(['mark' => 'common', 'mark_name' => '公共接口']);

    Route::group(function () {
        //用户类  用户coupons/order
        Route::get('user', [\app\api\controller\v1\user\UserController::class, 'user'])->name('user')->option(['real_name' => '个人中心']);//个人中心
        Route::post('user/spread', [\app\api\controller\v1\user\UserController::class, 'spread'])->name('userSpread')->option(['real_name' => '静默绑定授权']);//静默绑定授权
        Route::post('user/edit', [\app\api\controller\v1\user\UserController::class, 'edit'])->name('userEdit')->option(['real_name' => '用户修改信息']);//用户修改信息
        Route::get('user/balance', [\app\api\controller\v1\user\UserController::class, 'balance'])->name('userBalance')->option(['real_name' => '用户资金统计']);//用户资金统计
        Route::get('userinfo', [\app\api\controller\v1\user\UserController::class, 'userInfo'])->name('userinfo')->option(['real_name' => '用户信息']);// 用户信息
    })->option(['parent' => 'user', 'cate_name' => '用户中心']);

    Route::group(function () {
        //用户类  地址
        Route::get('address/detail/:id', [\app\api\controller\v1\user\UserAddressController::class, 'address'])->name('address')->option(['real_name' => '获取单个地址']);//获取单个地址
        Route::get('address/list', [\app\api\controller\v1\user\UserAddressController::class, 'addressList'])->name('addressList')->option(['real_name' => '地址列表']);//地址列表
        Route::post('address/default/set', [\app\api\controller\v1\user\UserAddressController::class, 'addressDefaultSet'])->name('addressDefaultSet')->option(['real_name' => '设置默认地址']);//设置默认地址
        Route::get('address/default', [\app\api\controller\v1\user\UserAddressController::class, 'addressDefault'])->name('addressDefault')->option(['real_name' => '获取默认地址']);//获取默认地址
        Route::post('address/edit', [\app\api\controller\v1\user\UserAddressController::class, 'addressEdit'])->name('addressEdit')->option(['real_name' => '修改/添加地址']);//修改 添加 地址
        Route::post('address/del', [\app\api\controller\v1\user\UserAddressController::class, 'addressDel'])->name('addressDel')->option(['real_name' => '删除地址']);//删除地址
    })->option(['parent' => 'user', 'cate_name' => '用户地址']);

    Route::group(function () { //用户类 收藏
        Route::get('collect/user', [\app\api\controller\v1\user\UserCollectController::class, 'collectUser'])->name('collectUser')->option(['real_name' => '收藏商品列表']);//收藏商品列表
        Route::post('collect/add', [\app\api\controller\v1\user\UserCollectController::class, 'collectAdd'])->name('collectAdd')->option(['real_name' => '添加收藏']);//添加收藏
        Route::post('collect/del', [\app\api\controller\v1\user\UserCollectController::class, 'collectDel'])->name('collectDel')->option(['real_name' => '取消收藏']);//取消收藏
        Route::post('collect/all', [\app\api\controller\v1\user\UserCollectController::class, 'collectAll'])->name('collectAll')->option(['real_name' => '批量添加收藏']);//批量添加收藏
    })->option(['parent' => 'user', 'cate_name' => '用户收藏']);

    Route::group(function () {
        Route::get('rank', [\app\api\controller\v1\user\UserController::class, 'rank'])->name('rank')->option(['real_name' => '公众号授权登录']);//推广人排行
        //用戶类 分享
        Route::post('user/share', [\app\api\controller\v1\PublicController::class, 'userShare'])->name('user_share')->option(['real_name' => '记录用户分享']);//记录用户分享
        Route::get('user/share/words', [\app\api\controller\v1\PublicController::class, 'copyShareWords'])->name('user_share_words')->option(['real_name' => '关键字分享']);//关键字分享
    })->option(['parent' => 'user', 'cate_name' => '用户分享']);

    // 签到路由已迁移至 modules/marketing/signin/route/user.php

    Route::group(function () {
        //优惠券类
        Route::post('coupon/receive', [\app\api\controller\v1\store\StoreCouponsController::class, 'receive'])->name('couponReceive')->option(['real_name' => '领取优惠券']); //领取优惠券
        Route::post('coupon/receive/batch', [\app\api\controller\v1\store\StoreCouponsController::class, 'receiveBatch'])->name('couponReceiveBatch')->option(['real_name' => '批量领取优惠券']); //批量领取优惠券
        Route::get('coupons/user/:types', [\app\api\controller\v1\store\StoreCouponsController::class, 'user'])->name('couponsUser')->option(['real_name' => '用户已领取优惠券']);//用户已领取优惠券
        Route::get('coupons/order/:price', [\app\api\controller\v1\store\StoreCouponsController::class, 'order'])->name('couponsOrder')->option(['real_name' => '优惠券 订单列表']);//优惠券 订单列表
    })->option(['mark' => 'coupons', 'mark_name' => '优惠券']);

    Route::group(function () {
        //购物车类
        Route::get('cart/list', [\app\api\controller\v1\store\StoreCartController::class, 'lst'])->name('cartList')->option(['real_name' => '购物车列表']); //购物车列表
        Route::post('cart/add', [\app\api\controller\v1\store\StoreCartController::class, 'add'])->name('cartAdd')->option(['real_name' => '购物车添加']); //购物车添加
        Route::post('cart/del', [\app\api\controller\v1\store\StoreCartController::class, 'del'])->name('cartDel')->option(['real_name' => '购物车删除']); //购物车删除
        Route::post('order/cancel', [\app\api\controller\v1\order\CenterOrderController::class, 'cancel'])->name('orderCancel')->option(['real_name' => '订单取消']); //订单取消
        Route::post('cart/num', [\app\api\controller\v1\store\StoreCartController::class, 'num'])->name('cartNum')->option(['real_name' => '购物车修改商品数量']); //购物车 修改商品数量
        Route::get('cart/count', [\app\api\controller\v1\store\StoreCartController::class, 'count'])->name('cartCount')->option(['real_name' => '购物车数量']); //购物车 获取数量
    })->option(['mark' => 'cart', 'mark_name' => '购物车']);

    Route::group(function () {
        //订单类
        Route::post('order/check_shipping', [\app\api\controller\v1\order\CenterOrderController::class, 'checkShipping'])->name('checkShipping')->option(['real_name' => '检测是否显示快递和自提标签']); //检测是否显示快递和自提标签
        // [已迁移至 CenterOrderController] Route::post('order/confirm', [\app\api\controller\v1\order\CenterOrderController::class, 'confirm'])->name('orderConfirm')->option(['real_name' => '订单确认']); //订单确认
        // [已迁移至 CenterOrderController] Route::post('order/computed/:key', [\app\api\controller\v1\order\CenterOrderController::class, 'computedOrder'])->name('computedOrder')->option(['real_name' => '计算订单金额']); //计算订单金额
        // [已迁移至 CenterOrderController] Route::post('order/create/:key', [\app\api\controller\v1\order\CenterOrderController::class, 'create'])->name('orderCreate')->middleware(BlockerMiddleware::class)->option(['real_name' => '订单创建']); //订单创建
        // [已迁移至 CenterOrderController] Route::get('order/data', [\app\api\controller\v1\order\CenterOrderController::class, 'data'])->name('orderData')->option(['real_name' => '订单统计数据']); //订单统计数据
        // [已迁移至 CenterOrderController] Route::get('order/list', [\app\api\controller\v1\order\CenterOrderController::class, 'lst'])->name('orderList')->option(['real_name' => '订单列表']); //订单列表
        // [已迁移至 CenterOrderController] Route::get('order/detail/:uni/[:cartId]', [\app\api\controller\v1\order\CenterOrderController::class, 'detail'])->name('orderDetail')->option(['real_name' => '订单详情']); //订单详情
        Route::get('order/refund_detail/:uni/[:cartId]', [\app\api\controller\v1\order\CenterOrderController::class, 'refundDetail'])->name('refundDetail')->option(['real_name' => '退款订单详情']); //退款订单详情
        Route::get('order/refund/reason', [\app\api\controller\v1\order\CenterOrderController::class, 'refundReason'])->name('orderRefundReason')->middleware(BlockerMiddleware::class)->option(['real_name' => '订单退款理由']); //订单退款理由
        //订单退款审核 无控制器方法
        // Route::post('order/refund/verify', [\app\api\controller\v1\order\CenterOrderController::class, 'refundVerify'])->name('orderRefundVerify')->middleware(BlockerMiddleware::class)->option(['real_name' => '订单退款审核']); //订单退款审核
        Route::post('order/take', [\app\api\controller\v1\order\CenterOrderController::class, 'take'])->name('orderTake')->middleware(BlockerMiddleware::class)->option(['real_name' => '订单收货']); //订单收货
        Route::get('order/express/:uni/[:type]', [\app\api\controller\v1\order\CenterOrderController::class, 'express'])->name('orderExpress')->option(['real_name' => '订单查看物流']); //订单查看物流
        Route::post('order/del', [\app\api\controller\v1\order\CenterOrderController::class, 'del'])->name('orderDel')->option(['real_name' => '订单删除']); //订单删除
        Route::post('order/again', [\app\api\controller\v1\order\CenterOrderController::class, 'again'])->name('orderAgain')->option(['real_name' => '再次下单']); //订单 再次下单
        Route::post('order/pay', [\app\api\controller\v1\order\CenterOrderController::class, 'pay'])->name('orderPay')->option(['real_name' => '订单支付']); //订单支付
        Route::post('order/product', [\app\api\controller\v1\order\CenterOrderController::class, 'product'])->name('orderProduct')->option(['real_name' => '订单商品信息']); //订单商品信息
        Route::post('order/comment', [\app\api\controller\v1\order\CenterOrderController::class, 'comment'])->name('orderComment')->option(['real_name' => '订单评价']); //订单评价
        Route::get('order/cashier/:orderNo/[:type]', [\app\api\controller\v1\order\CenterOrderController::class, 'cashier'])->name('orderCashier')->option(['real_name' => '订单收银台']); //订单收银台
        Route::get('order/friend_detail', [\app\api\controller\v1\order\CenterOrderController::class, 'friendDetail'])->name('friendDetail')->option(['real_name' => '代付详情']);//代付详情
        Route::post('order/receive_gift/:oid', [\app\api\controller\v1\order\CenterOrderController::class, 'receiveGift'])->name('receiveGift')->option(['real_name' => '领取礼物']);//领取礼物
        Route::get('order/gift_detail/:oid', [\app\api\controller\v1\order\CenterOrderController::class, 'giftDetail'])->name('giftDetail')->option(['real_name' => '礼品详情']); //礼品详情

    })->option(['mark' => 'order', 'mark_name' => '订单']);

    // 砍价参与/助力路由已迁移至 modules/trade/bargain/route/user.php

    // 拼团开团/海报路由已迁移至 modules/trade/combination/route/user.php（含 seckill/code 迁至 seckill 模块）

    Route::group(function () {
        //账单类
        Route::post('spread/people', [\app\api\controller\v1\user\UserController::class, 'spreadPeople'])->name('spreadPeople')->option(['real_name' => '推荐用户']);//推荐用户
        Route::post('spread/order', [\app\api\controller\v1\user\UserBillController::class, 'spreadOrder'])->name('spreadOrder')->option(['real_name' => '推广订单']);//推广订单
        Route::get('spread/commission/:type', [\app\api\controller\v1\user\UserBillController::class, 'spreadCommission'])->name('spreadCommission')->option(['real_name' => '推广佣金明细']);//推广佣金明细
        Route::get('spread/count/:type', [\app\api\controller\v1\user\UserBillController::class, 'spreadCount'])->name('spreadCount')->option(['real_name' => '推广佣金']);//推广 佣金 3/提现 4 总和
        Route::get('spread/banner', [\app\api\controller\v1\user\UserBillController::class, 'spreadBanner'])->name('spreadBanner')->option(['real_name' => '推广分销二维码海报生成']);//推广分销二维码海报生成
        Route::get('integral/list', [\app\api\controller\v1\user\UserBillController::class, 'integralList'])->name('integralList')->option(['real_name' => '积分记录']);//积分记录
        Route::get('user/routine_code', [\app\api\controller\v1\user\UserBillController::class, 'getRoutineCode'])->name('getRoutineCode')->option(['real_name' => '小程序二维码']);//小程序二维码
        Route::get('user/spread_info', [\app\api\controller\v1\user\UserBillController::class, 'getSpreadInfo'])->name('getSpreadInfo')->option(['real_name' => '获取分销背景等信息']);//获取分销背景等信息
        Route::post('division/order', [\app\api\controller\v1\user\UserBillController::class, 'divisionOrder'])->name('divisionOrder')->option(['real_name' => '事业部推广订单']);//事业部推广订单
    })->option(['mark' => 'division', 'mark_name' => '账单']);

    Route::group(function () {
        //提现类
        Route::get('extract/bank', [\app\api\controller\v1\user\UserExtractController::class, 'bank'])->name('extractBank')->option(['real_name' => '提现银行']);//提现银行/提现最低金额
        Route::post('extract/cash', [\app\api\controller\v1\user\UserExtractController::class, 'cash'])->name('extractCash')->option(['real_name' => '提现申请']);//提现申请
    })->option(['mark' => 'extract', 'mark_name' => '提现']);

    Route::group(function () {
        //会员等级类
        Route::get('user/level/detection', [\app\api\controller\v1\user\UserLevelController::class, 'detection'])->name('userLevelDetection')->option(['real_name' => '检测用户是否可以成为会员']);//检测用户是否可以成为会员
        Route::get('user/level/grade', [\app\api\controller\v1\user\UserLevelController::class, 'grade'])->name('userLevelGrade')->option(['real_name' => '会员等级列表']);//会员等级列表
        Route::get('user/level/task/:id', [\app\api\controller\v1\user\UserLevelController::class, 'task'])->name('userLevelTask')->option(['real_name' => '获取等级任务']);//获取等级任务
        Route::get('user/level/info', [\app\api\controller\v1\user\UserLevelController::class, 'userLevelInfo'])->name('levelInfo')->option(['real_name' => '获取等级任务']);//获取等级任务
        Route::get('user/level/expList', [\app\api\controller\v1\user\UserLevelController::class, 'expList'])->name('expList')->option(['real_name' => '获取等级任务']);//获取等级任务
    })->option(['mark' => 'user_level', 'mark_name' => '会员等级']);

    Route::group(function () {
        //会员卡
        Route::get('user/member/card/index', [\app\api\controller\v1\user\MemberCardController::class, 'index'])->name('userMemberCardIndex')->option(['real_name' => '主页会员权益介绍页']);// 主页会员权益介绍页
        Route::post('user/member/card/draw', [\app\api\controller\v1\user\MemberCardController::class, 'drawMemberCard'])->name('userMemberCardDraw')->option(['real_name' => '卡密领取会员卡']);//卡密领取会员卡
        Route::get('user/member/coupons/list', [\app\api\controller\v1\user\MemberCardController::class, 'memberCouponList'])->name('userMemberCouponsList')->option(['real_name' => '会员券列表']);//会员券列表
        Route::get('user/member/overdue/time', [\app\api\controller\v1\user\MemberCardController::class, 'getOverdueTime'])->name('userMemberOverdueTime')->option(['real_name' => '会员时间']);//会员时间
        //购买会员卡创建订单
        Route::post('user/member/card/create', [\app\api\controller\v1\user\MemberCardController::class, 'createOrder'])->name('userMemberCardCreate')->option(['real_name' => '购买卡创建订单']);
        //创建线下收银收款单
        Route::post('offline_receipt/create', [\app\api\controller\v1\user\OfflineReceiptController::class, 'create'])->name('payOfflineCreate')->option(['real_name' => '创建线下收银收款单']);
        //计算线下收银会员优惠价
        Route::post('offline_receipt/check/price', [\app\api\controller\v1\user\OfflineReceiptController::class, 'computedOfflinePayPrice'])->name('offlineCheckPrice')->option(['real_name' => '计算线下收银会员优惠价']);
        //线下收银支付方式开关
        Route::get('offline_receipt/pay/type', [\app\api\controller\v1\user\OfflineReceiptController::class, 'payType'])->name('offlinePayType')->option(['real_name' => '线下收银支付方式']);
    })->option(['parent' => 'user', 'cate_name' => '会员卡']);

    Route::group(function () {
        //消息站内信（兼容旧路径，前端迁移完成后删除）
        Route::get('user/message_system/list', [\app\api\controller\v1\user\MessageSystemController::class, 'messageList'])->name('MessageSystemList')->option(['real_name' => '站内信列表']); //站内信列表
        Route::get('user/message_system/detail/:id', [\app\api\controller\v1\user\MessageSystemController::class, 'detail'])->name('MessageSystemDetail')->option(['real_name' => '详情']); //详情
        Route::get('user/message_system/edit_message', [\app\api\controller\v1\user\MessageSystemController::class, 'editMessage'])->name('EditMessage')->option(['real_name' => '站内信设置']);//站内信设为未读/删除
    })->option(['mark' => 'message_system', 'mark_name' => '站内信']);

    // 站内信新接口（api/notice/*）
    Route::group('notice', function () {
        Route::get('', [\app\api\controller\v1\message\NoticeController::class, 'index'])->option(['real_name' => '站内信列表']);
        Route::get('unread-count', [\app\api\controller\v1\message\NoticeController::class, 'unreadCount'])->option(['real_name' => '未读数量']);
        Route::get(':id', [\app\api\controller\v1\message\NoticeController::class, 'detail'])->option(['real_name' => '站内信详情']);
        Route::post('read/:id', [\app\api\controller\v1\message\NoticeController::class, 'read'])->option(['real_name' => '标记已读']);
        Route::post('batch-read', [\app\api\controller\v1\message\NoticeController::class, 'batchRead'])->option(['real_name' => '批量标记已读']);
        Route::post('read-all', [\app\api\controller\v1\message\NoticeController::class, 'readAll'])->option(['real_name' => '全部标记已读']);
        Route::delete(':id', [\app\api\controller\v1\message\NoticeController::class, 'delete'])->option(['real_name' => '删除站内信']);
        Route::post('batch-delete', [\app\api\controller\v1\message\NoticeController::class, 'batchDelete'])->option(['real_name' => '批量删除']);
    })->option(['mark' => 'notice', 'mark_name' => '站内信']);

    // 积分商城订单路由已迁移至 modules/trade/integral/route/user.php

    Route::group(function () {
        /** 退款相关 */
        Route::get('order/refund/cart_info/:orderNo', [\app\api\controller\v1\order\CenterOrderController::class, 'refundCartInfo'])->name('refundCartInfo')->option(['real_name' => '退款中间页面订单商品列表']);//退款中间页面订单商品列表
        Route::post('order/refund/cart_info', [\app\api\controller\v1\order\CenterOrderController::class, 'refundCartInfoList'])->name('StoreOrderRefundCartInfoList')->option(['real_name' => '获取退款商品列表']);//获取退款商品列表
        Route::get('order/refund/form_config/:orderNo', [\app\api\controller\v1\order\CenterOrderController::class, 'refundFormConfig'])->name('refundFormConfig')->option(['real_name' => '获取退款表单配置']);//获取退款表单配置
        Route::post('order/refund/apply/:orderNo', [\app\api\controller\v1\order\CenterOrderController::class, 'applyRefund'])->name('StoreOrderApplyRefund')->option(['real_name' => '订单申请退款']);//订单申请退款
        // 旧退款单路由（refund/list|detail|cancel|express|del）已下线，统一迁移至 after_sale/* 售后路由组
    })->option(['mark' => 'refund', 'mark_name' => '售后']);

    Route::group(function () {
        /** 售后相关（独立于退款流程，按产品类型 getAfterSaleRule 三维规则分流） */
        Route::get('after_sale/available_types/:orderNo', [\app\api\controller\v1\order\AfterSaleController::class, 'availableTypes'])->name('afterSaleAvailableTypes')->option(['real_name' => '查询订单可用售后类型']);//查询订单可用售后类型
        Route::get('after_sale/form_config/:orderNo/:type', [\app\api\controller\v1\order\AfterSaleController::class, 'formConfig'])->name('afterSaleFormConfig')->option(['real_name' => '获取售后表单配置']);//获取售后表单配置（数据驱动前端）
        Route::get('after_sale/detail/:afterSaleNo', [\app\api\controller\v1\order\AfterSaleController::class, 'detail'])->name('afterSaleDetail')->option(['real_name' => '售后单详情']);//售后单详情（含时间线+可操作按钮）
        Route::get('after_sale/list', [\app\api\controller\v1\order\AfterSaleController::class, 'list'])->name('afterSaleList')->option(['real_name' => '用户售后列表']);//用户售后列表
        Route::post('after_sale/apply', [\app\api\controller\v1\order\AfterSaleController::class, 'apply'])->name('afterSaleApply')->middleware(BlockerMiddleware::class)->option(['real_name' => '用户提交售后申请']);//用户提交售后申请
        Route::post('after_sale/cancel', [\app\api\controller\v1\order\AfterSaleController::class, 'cancel'])->name('afterSaleCancel')->option(['real_name' => '用户取消售后']);//用户取消售后
        Route::post('after_sale/deliver_return', [\app\api\controller\v1\order\AfterSaleController::class, 'deliverReturn'])->name('afterSaleDeliverReturn')->option(['real_name' => '用户填写退货物流']);//用户填写退货物流
        Route::post('after_sale/confirm_receive', [\app\api\controller\v1\order\AfterSaleController::class, 'confirmReceive'])->name('afterSaleConfirmReceive')->option(['real_name' => '用户确认收货']);//用户确认收货（补发/维修完成后）
    })->option(['mark' => 'after_sale', 'mark_name' => '售后']);

    // 代理商相关路由已迁移至 modules/distribution/route/user.php

    Route::group(function () {
        /** 佣金相关 */
        Route::get('commission', [\app\api\controller\v1\user\UserBrokerageController::class, 'commission'])->name('commission')->option(['real_name' => '推广数据']);//推广数据 昨天的佣金 累计提现金额 当前佣金
        Route::get('brokerage_rank', [\app\api\controller\v1\user\UserBrokerageController::class, 'brokerageRank'])->name('brokerageRank')->option(['real_name' => '佣金排行']);//佣金排行
        /** 用户注销 */
        Route::get('user_cancel', [\app\api\controller\v1\user\UserController::class, 'SetUserCancel'])->name('SetUserCancel')->option(['real_name' => '用户注销']);//用户注销
        /** 用户浏览记录 */
        Route::get('user/visit_list', [\app\api\controller\v1\user\UserController::class, 'visitList'])->name('visitList')->option(['real_name' => '商品浏览列表']);//商品浏览列表
        Route::delete('user/visit', [\app\api\controller\v1\user\UserController::class, 'visitDelete'])->name('visitDelete')->option(['real_name' => '商品浏览记录删除']);//商品浏览记录删除
    })->option(['mark' => 'user', 'mark_name' => '用户']);

    // 分销员申请路由已迁移至 modules/distribution/route/user.php

})->middleware(\app\http\middleware\AllowOriginMiddleware::class)->middleware(\app\api\middleware\StationOpenMiddleware::class)->middleware(\app\api\middleware\AuthTokenMiddleware::class, true);
//未授权接口
Route::group(function () {
    Route::group(function () {
        Route::get('menu/user', [\app\api\controller\v1\PublicController::class, 'menuUser'])->name('menuUser')->option(['real_name' => '个人中心菜单']);//个人中心菜单
        //公共类
        Route::get('index', [\app\api\controller\v1\PublicController::class, 'index'])->name('index')->option(['real_name' => '首页']);//首页
        Route::get('site_config', [\app\api\controller\v1\PublicController::class, 'getSiteConfig'])->name('getSiteConfig')->option(['real_name' => '获取网站配置']);//获取网站配置
        //DIY接口
        Route::get('diy/get_diy/[:id]', [\app\api\controller\v1\PublicController::class, 'getDiy']);
        Route::get('home/products', [\app\api\controller\v1\PublicController::class, 'homeProductsList'])->name('homeProductsList')->option(['real_name' => '获取首页推荐不同类型商品的轮播图和商品']);//获取首页推荐不同类型商品的轮播图和商品

        Route::get('theme_info/:type', [\app\api\controller\v1\PublicController::class, 'themeInfo'])->name('themeInfo')->option(['real_name' => '主题详情']);
        Route::get('theme_version', [\app\api\controller\v1\PublicController::class, 'themeVersion'])->name('themeVersion')->option(['real_name' => '主题版本']);
        Route::get('theme/user', [\app\api\controller\v1\PublicController::class, 'themeUser'])->name('themeUser')->option(['real_name' => '自定义组件-用户']);
        Route::get('theme/article', [\app\api\controller\v1\PublicController::class, 'themeArticle'])->name('themeArticle')->option(['real_name' => '自定义组件-文章']);
        Route::get('theme/coupon', [\app\api\controller\v1\PublicController::class, 'themeCoupon'])->name('themeCoupon')->option(['real_name' => '自定义组件-优惠券']);
        Route::get('theme/product', [\app\api\controller\v1\PublicController::class, 'themeProduct'])->name('themeProduct')->option(['real_name' => '自定义组件-商品']);
        Route::get('theme/navigation', [\app\api\controller\v1\PublicController::class, 'themeNavigation'])->name('themeNavigation')->option(['real_name' => '导航']);

    })->option(['mark' => 'index', 'mark_name' => '主页接口']);

    Route::group(function () {
        Route::get('search/keyword', [\app\api\controller\v1\PublicController::class, 'search'])->name('searchKeyword')->option(['real_name' => '热门搜索关键字获取']);//热门搜索关键字获取
        //商品分类类
        Route::get('category', [\app\api\controller\v1\store\CategoryController::class, 'category'])->name('category')->option(['real_name' => '商品分类类']);
        Route::get('category_version', [\app\api\controller\v1\store\CategoryController::class, 'getCategoryVersion'])->name('getCategoryVersion')->option(['real_name' => '商品分类类版本']);//商品分类类版本

        //商品类
        Route::post('image_base64', [\app\api\controller\v1\PublicController::class, 'getImageBase64'])->name('getImageBase64')->option(['real_name' => '获取图片base64']);// 获取图片base64
        Route::get('product/detail/:id', [\app\api\controller\v1\product\CenterProductController::class, 'detail'])->name('detail')->option(['real_name' => '商品详情']);//商品详情
        Route::get('products', [\app\api\controller\v1\product\CenterProductController::class, 'productList'])->name('products')->option(['real_name' => '商品列表']);//商品列表
        Route::get('groom/list/:type', [\app\api\controller\v1\store\StoreProductController::class, 'groomList'])->name('groomList')->option(['real_name' => '会员商品推荐']);//会员商品推荐
        Route::get('product/hot', [\app\api\controller\v1\store\StoreProductController::class, 'productHot'])->name('productHot')->option(['real_name' => '为你推荐']);//为你推荐
        Route::get('reply/list/:id', [\app\api\controller\v1\store\StoreProductController::class, 'replyList'])->name('replyList')->option(['real_name' => '商品评价列表']);//商品评价列表
        Route::get('reply/config/:id', [\app\api\controller\v1\store\StoreProductController::class, 'replyConfig'])->name('replyConfig')->option(['real_name' => '商品评价数量和好评度']);//商品评价数量和好评度
        Route::get('advance/list', [\app\api\controller\v1\store\StoreProductController::class, 'advanceList'])->name('advanceList')->option(['real_name' => '预售商品列表']);//预售商品列表
        Route::get('product/code/:id', [\app\api\controller\v1\store\StoreProductController::class, 'code'])->name('productCode')->option(['real_name' => '商品分享二维码']);//商品分享二维码 推广员
        Route::get('product/real_price/:id/:unique', [\app\api\controller\v1\product\CenterProductController::class, 'realPrice'])->name('realPrice')->option(['real_name' => '商品到手价']);//商品到手价（含规格唯一值）
        Route::get('product/real_price/:id', [\app\api\controller\v1\product\CenterProductController::class, 'realPrice'])->name('realPriceNoUnique')->option(['real_name' => '商品到手价']);//商品到手价
        Route::get('product/real_price/:id/', [\app\api\controller\v1\product\CenterProductController::class, 'realPrice'])->name('realPriceSlash')->option(['real_name' => '商品到手价']);//商品到手价（尾斜线）
    })->option(['mark' => 'product', 'mark_name' => '商品']);

    Route::group(function () {

        Route::group(function () {
            //文章分类类
            Route::get('article/category/list', [\app\api\controller\v1\article\ArticleCategoryController::class, 'lst'])->name('articleCategoryList')->option(['real_name' => '文章分类列表']);//文章分类列表
            //文章类
            Route::get('article/list/:cid', [\app\api\controller\v1\article\ArticleController::class, 'lst'])->name('articleList')->option(['real_name' => '文章列表']);//文章列表
            Route::get('article/details/:id', [\app\api\controller\v1\article\ArticleController::class, 'details'])->name('articleDetails')->option(['real_name' => '文章详情']);//文章详情
            Route::get('article/hot/list', [\app\api\controller\v1\article\ArticleController::class, 'hot'])->name('articleHotList')->option(['real_name' => '文章 热门']);//文章 热门
            Route::get('article/new/list', [\app\api\controller\v1\article\ArticleController::class, 'new'])->name('articleNewList')->option(['real_name' => '文章 最新']);//文章 最新
            Route::get('article/banner/list', [\app\api\controller\v1\article\ArticleController::class, 'banner'])->name('articleBannerList')->option(['real_name' => '文章 banner']);//文章 banner
        })->option(['parent' => 'activity_nologin', 'cate_name' => '文章(未授权)']);

        // 秒杀商品浏览路由已迁移至 modules/trade/seckill/route/user.php

        // 砍价商品浏览路由已迁移至 modules/trade/bargain/route/user.php

        // 拼团商品浏览路由已迁移至 modules/trade/combination/route/user.php

        //用户类
        Route::get('user/activity', [\app\api\controller\v1\user\UserController::class, 'activity'])->name('userActivity')->option(['real_name' => '活动状态']);//活动状态

    })->option(['mark' => 'activity_nologin', 'mark_name' => '活动']);

    Route::group(function () {
        //物流公司
        Route::get('logistics', [\app\api\controller\v1\PublicController::class, 'logistics'])->name('logistics')->option(['real_name' => '物流公司列表']);//物流公司列表

        //分享配置
        Route::get('share', [\app\api\controller\v1\PublicController::class, 'share'])->name('share')->option(['real_name' => '分享配置']);//分享配置

        //优惠券
        Route::get('coupons', [\app\api\controller\v1\store\StoreCouponsController::class, 'lst'])->name('couponsList')->option(['real_name' => '可领取优惠券列表']); //可领取优惠券列表

        //短信购买异步通知
        Route::post('sms/pay/notify', [\app\api\controller\v1\PublicController::class, 'smsPayNotify'])->name('smsPayNotify')->option(['real_name' => '短信购买异步通知']); //短信购买异步通知

        //获取关注微信公众号海报
        //用户是否关注
        Route::get('subscribe', [\app\api\controller\v1\user\UserController::class, 'subscribe'])->name('Subscribe')->option(['real_name' => '用户是否关注']);
        //门店列表
        Route::get('store_list', [\app\api\controller\v1\PublicController::class, 'storeList'])->name('storeList')->option(['real_name' => '门店列表']);
        //获取城市列表
        Route::get('city_list', [\app\api\controller\v1\PublicController::class, 'cityList'])->name('cityList')->option(['real_name' => '获取城市列表']);
        //拼团数据
        Route::get('pink', [\app\api\controller\v1\PublicController::class, 'pink'])->name('pinkData')->option(['real_name' => '拼团数据']);
        //获取底部导航
        Route::get('navigation/[:template_name]', [\app\api\controller\v1\PublicController::class, 'getNavigation'])->name('getNavigation')->option(['real_name' => '获取底部导航']);
        //用户访问
        Route::post('user/set_visit', [\app\api\controller\v1\user\UserController::class, 'setVisit'])->name('setVisit')->option(['real_name' => '添加用户访问记录']);// 添加用户访问记录
        //复制口令接口
        Route::get('copy_words', [\app\api\controller\v1\PublicController::class, 'copyWords'])->name('copyWords')->option(['real_name' => '复制口令接口']);// 复制口令接口
        //获取网站配置
        Route::get('site_config', [\app\api\controller\v1\PublicController::class, 'getSiteConfig'])->name('getSiteConfig')->option(['real_name' => '获取网站配置']);//获取网站配置
    })->option(['mark' => 'setting', 'mark_name' => '商城配置']);

    // 积分商城商品浏览路由已迁移至 modules/trade/integral/route/user.php

    Route::group(function () {
        //获取app最新版本
        Route::get('get_new_app/:platform', [\app\api\controller\v1\PublicController::class, 'getNewAppVersion'])->name('getNewAppVersion')->option(['real_name' => '获取app最新版本']);//获取app最新版本
        //获取客服类型
        Route::get('get_customer_type', [\app\api\controller\v1\PublicController::class, 'getCustomerType'])->name('getCustomerType')->option(['real_name' => '获取客服类型']);//获取客服类型
        //长链接设置
        Route::get('get_workerman_url', [\app\api\controller\v1\PublicController::class, 'getWorkerManUrl'])->name('getWorkerManUrl')->option(['real_name' => '长链接设置']);
        //首页开屏广告
        Route::get('get_open_adv', [\app\api\controller\v1\PublicController::class, 'getOpenAdv'])->name('getOpenAdv')->option(['real_name' => '首页开屏广告']);
        //获取用户协议
        Route::get('user_agreement', [\app\api\controller\v1\PublicController::class, 'getUserAgreement'])->name('getUserAgreement')->option(['real_name' => '获取用户协议']);
        //获取协议
        Route::get('get_agreement/:type', [\app\api\controller\v1\PublicController::class, 'getAgreement'])->name('getAgreement')->option(['real_name' => '获取协议']);

    })->option(['mark' => 'other', 'mark_name' => '其他接口']);

    Route::group(function () {
        //获取多语言类型列表
        Route::get('get_lang_type_list', [\app\api\controller\v1\PublicController::class, 'getLangTypeList'])->name('getLangTypeList')->option(['real_name' => '获取多语言类型列表']);
        //获取当前语言json
        Route::get('get_lang_json', [\app\api\controller\v1\PublicController::class, 'getLangJson'])->name('getLangJson')->option(['real_name' => '获取当前语言json']);
        //获取当前后台设置的默认语言类型
        Route::get('get_default_lang_type', [\app\api\controller\v1\PublicController::class, 'getDefaultLangType'])->name('getLangJson')->option(['real_name' => '获取当前后台设置的默认语言类型']);
        //获取当前后台设置的默认语言类型
        Route::get('lang_version', [\app\api\controller\v1\PublicController::class, 'getLangVersion'])->name('getLangVersion')->option(['real_name' => '获取当前后台设置的默认语言类型']);
    })->option(['mark' => 'lang', 'mark_name' => '多语言']);

    Route::group(function () {
        /** 定时任务接口 */
        //定时任务调用接口
        Route::get('crontab/run', [\app\api\controller\v1\CrontabController::class, 'crontabRun'])->name('crontabRun')->option(['real_name' => '定时任务调用接口']);
        //检测定时任务接口
        Route::get('crontab/check', [\app\api\controller\v1\CrontabController::class, 'crontabCheck'])->name('crontabCheck')->option(['real_name' => '检测定时任务接口']);
        //未支付自动取消订单
        Route::get('crontab/order_cancel', [\app\api\controller\v1\CrontabController::class, 'orderUnpaidCancel'])->name('orderUnpaidCancel')->option(['real_name' => '未支付自动取消订单']);
        //拼团到期订单处理
        Route::get('crontab/pink_expiration', [\app\api\controller\v1\CrontabController::class, 'pinkExpiration'])->name('pinkExpiration')->option(['real_name' => '拼团到期订单处理']);
        //自动解绑上级绑定
        Route::get('crontab/distribution_unbind', [\app\api\controller\v1\CrontabController::class, 'distributionUnbind'])->name('distributionUnbind')->option(['real_name' => '自动解绑上级绑定']);
        //更新直播商品状态
        Route::get('crontab/live_product_status', [\app\api\controller\v1\CrontabController::class, 'syncGoodStatus'])->name('syncGoodStatus')->option(['real_name' => '更新直播商品状态']);
        //更新直播间状态
        Route::get('crontab/live_room_status', [\app\api\controller\v1\CrontabController::class, 'syncRoomStatus'])->name('syncRoomStatus')->option(['real_name' => '更新直播间状态']);
        //自动收货
        Route::get('crontab/take_delivery', [\app\api\controller\v1\CrontabController::class, 'autoTakeOrder'])->name('autoTakeOrder')->option(['real_name' => '自动收货']);
        //查询预售到期商品自动下架
        Route::get('crontab/advance_off', [\app\api\controller\v1\CrontabController::class, 'downAdvance'])->name('downAdvance')->option(['real_name' => '查询预售到期商品自动下架']);
        //自动好评
        Route::get('crontab/product_replay', [\app\api\controller\v1\CrontabController::class, 'autoComment'])->name('autoComment')->option(['real_name' => '自动好评']);
        //清除昨日海报
        Route::get('crontab/clear_poster', [\app\api\controller\v1\CrontabController::class, 'emptyYesterdayAttachment'])->name('emptyYesterdayAttachment')->option(['real_name' => '清除昨日海报']);

    })->option(['mark' => 'crontab', 'mark_name' => '定时任务']);

})->middleware(\app\http\middleware\AllowOriginMiddleware::class)
    ->middleware(\app\api\middleware\StationOpenMiddleware::class)
    ->middleware(\app\api\middleware\AuthTokenMiddleware::class, false);

/**
 * v2 版本路由
 */
Route::group('v2', function () {
    //无需授权接口
    Route::group(function () {
        Route::group(function () {
            Route::get('diy/get_store_status', [\app\api\controller\v1\PublicController::class, 'getStoreStatus'])->option(['real_name' => '获取门店自提开启状态']);
            Route::get('diy/color_change/:name', [\app\api\controller\v1\PublicController::class, 'colorChange'])->option(['real_name' => '一键换色']);
            Route::get('diy/get_diy/[:name]', [\app\api\controller\v1\PublicController::class, 'getDiyV2'])->option(['real_name' => '获取DIY数据']);
            Route::get('diy/get_version/[:name]', [\app\api\controller\v1\PublicController::class, 'getVersionV2'])->option(['real_name' => '获取DIY版本号']);
        })->option(['mark' => 'diy', 'mark_name' => 'DIY']);
    });
    //需要授权
    Route::group(function () {

        Route::post('reset_cart', [\app\api\controller\v1\store\StoreCartController::class, 'resetCart'])->name('resetCart')->option(['real_name' => '清除购物车', 'mark' => 'cart', 'mark_name' => '购物车']);
        Route::get('new_coupon', [\app\api\controller\v1\store\StoreCouponsController::class, 'getNewCoupon'])->name('getNewCoupon')->option(['real_name' => '获取新人券', 'mark' => 'coupons', 'mark_name' => '优惠券']);//获取新人券
        Route::post('order/product_coupon/:orderId', [\app\api\controller\v1\store\StoreCouponsController::class, 'getOrderProductCoupon'])->option(['real_name' => '获取订单下管理的优惠券', 'mark' => 'coupons', 'mark_name' => '优惠券']);
        Route::get('cart_list', [\app\api\controller\v1\store\StoreCartController::class, 'getCartList'])->option(['real_name' => '获取购物车列表', 'mark' => 'cart', 'mark_name' => '购物车']);
        Route::get('get_attr/:id/:type', [\app\api\controller\v1\store\StoreProductController::class, 'getProductAttr'])->option(['real_name' => '获取商品规格', 'mark' => 'cart', 'mark_name' => '购物车']);
        Route::post('set_cart_num', [\app\api\controller\v1\store\StoreCartController::class, 'setCartNum'])->option(['real_name' => '获取购物车数量', 'mark' => 'cart', 'mark_name' => '购物车']);

        Route::group(function () {
            //订单申请发票
            Route::post('order/make_up_invoice', [\app\api\controller\v1\order\StoreOrderInvoiceController::class, 'makeUp'])->name('orderMakeUpInvoice')->option(['real_name' => '订单申请发票']);
            //用户发票列表
            Route::get('invoice', [\app\api\controller\v1\user\UserInvoiceController::class, 'invoiceList'])->name('userInvoiceLIst')->option(['real_name' => '用户发票列表']);
            //单个发票详情
            Route::get('invoice/detail/:id', [\app\api\controller\v1\user\UserInvoiceController::class, 'invoice'])->name('userInvoiceDetail')->option(['real_name' => '单个发票详情']);
            //修改|添加发票
            Route::post('invoice/save', [\app\api\controller\v1\user\UserInvoiceController::class, 'saveInvoice'])->name('userInvoiceSave')->option(['real_name' => '修改|添加发票']);
            //设置默认发票
            Route::post('invoice/set_default/:id', [\app\api\controller\v1\user\UserInvoiceController::class, 'setDefaultInvoice'])->name('userInvoiceSetDefault')->option(['real_name' => '设置默认发票']);
            //获取默认发票
            Route::get('invoice/get_default/:type', [\app\api\controller\v1\user\UserInvoiceController::class, 'getDefaultInvoice'])->name('userInvoiceGetDefault')->option(['real_name' => '获取默认发票']);
            //删除发票
            Route::get('invoice/del/:id', [\app\api\controller\v1\user\UserInvoiceController::class, 'delInvoice'])->name('userInvoiceDel')->option(['real_name' => '删除发票']);
            //订单申请开票记录
            Route::get('order/invoice_list', [\app\api\controller\v1\order\StoreOrderInvoiceController::class, 'list'])->name('orderInvoiceList')->option(['real_name' => '订单申请开票记录']);
            //订单开票详情
            Route::get('order/invoice_detail/:uni', [\app\api\controller\v1\order\StoreOrderInvoiceController::class, 'detail'])->name('orderInvoiceDetailV2')->option(['real_name' => '订单开票详情']);
            //下载电子发票
            Route::get('order/down_invoice/:id', [\app\api\controller\v1\order\StoreOrderInvoiceController::class, 'downInvoice'])->name('downInvoice')->option(['real_name' => '下载电子发票']);
        })->option(['mark' => 'invoice', 'mark_name' => '发票']);

        //清除搜索记录
        Route::get('user/clean_search', [\app\api\controller\v1\user\UserSearchController::class, 'cleanUserSearch'])->name('cleanUserSearch')->option(['real_name' => '清除搜索记录']);

        // 抽奖路由已迁移至 modules/marketing/lottery/route/user.php（保持 v2 前缀）
        // 分销等级路由已迁移至 modules/distribution/route/user.php（保持 v2 前缀）

    })->middleware(\app\api\middleware\AuthTokenMiddleware::class, true);

    //授权不通过,不会抛出异常继续执行
    Route::group(function () {
        Route::get('user/search_list', [\app\api\controller\v1\user\UserSearchController::class, 'getUserSeachList'])->name('userSearchList')->option(['real_name' => '用户搜索记录']);
        Route::get('get_today_coupon', [\app\api\controller\v1\store\StoreCouponsController::class, 'getTodayCoupon'])->option(['real_name' => '新优惠券弹窗接口']);//新优惠券弹窗接口
        Route::get('subscribe', [\app\api\controller\v1\PublicController::class, 'subscribeV2'])->name('WechatSubscribeV2')->option(['real_name' => '微信公众号用户是否关注']);// 微信公众号用户是否关注
        Route::get('index', [\app\api\controller\v1\PublicController::class, 'indexV2'])->name('indexV2')->option(['real_name' => '首页']);//首页
        Route::get('coupons', [\app\api\controller\v1\store\StoreCouponsController::class, 'lstV2'])->name('couponsListV2')->option(['real_name' => '可领取优惠券列表']); //可领取优惠券列表
        Route::get('diy/sign', [\app\api\controller\v1\PublicController::class, 'getDiySign'])->name('getDiySign')->option(['real_name' => '获取Diy签到']);
    })->middleware(\app\api\middleware\AuthTokenMiddleware::class, false)
        ->option(['mark' => 'common', 'mark_name' => '公共接口']);

})->middleware(\app\http\middleware\AllowOriginMiddleware::class)->middleware(\app\api\middleware\StationOpenMiddleware::class);

// 自动加载模块用户端路由（一/二级目录扫描，含模块启用状态校验，禁用模块不加载路由）
\crmeb\plugin\ModuleRouteLoader::load('user');

/**
 * miss 路由
 *
 * 插件未安装/未启用时给出友好提示，避免前端拿到裸 404 无法区分"接口不存在"与"插件未安装"。
 * 模块判定基于目录扫描 + 安装状态校验（ModuleRouteLoader::inactiveModuleHint），不硬编码模块清单。
 */
Route::miss(function () {
    if (app()->request->isOptions()) {
        $header = Config::get('cookie.header');
        unset($header['Access-Control-Allow-Credentials']);
        return Response::create('ok')->code(200)->header($header);
    }
    $inactiveModule = \crmeb\plugin\ModuleRouteLoader::inactiveModuleHint('user');
    if ($inactiveModule !== null) {
        return app('json')->fail('插件 [' . $inactiveModule['name'] . '] 未安装或未启用，请先安装/启用后再访问');
    }
    return Response::create()->code(404);
});
