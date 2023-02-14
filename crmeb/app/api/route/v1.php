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
use think\facade\Config;
use think\Response;

Route::any('wechat/serve', 'v1.wechat.WechatController/serve');//公众号服务
Route::any('wechat/notify', 'v1.wechat.WechatController/notify');//公众号支付回调
Route::any('wechat/v3notify', 'v1.wechat.WechatController/v3notify');//公众号支付回调
Route::any('routine/notify', 'v1.wechat.AuthController/notify');//小程序支付回调
Route::any('pay/notify/:type', 'v1.PayController/notify');//支付回调
Route::get('get_script', 'v1.PublicController/getScript');//获取统计代码
Route::get('version', 'v1.PublicController/getVersion');//获取统计代码

Route::group(function () {
    //apple快捷登陆
    Route::post('apple_login', 'v1.LoginController/appleLogin')->name('appleLogin');//微信APP授权
    //账号密码登录
    Route::post('login', 'v1.LoginController/login')->name('login');
    // 获取发短信的key
    Route::get('verify_code', 'v1.LoginController/verifyCode')->name('verifyCode');
    //手机号登录
    Route::post('login/mobile', 'v1.LoginController/mobile')->name('loginMobile');
    //图片验证码
    Route::get('sms_captcha', 'v1.LoginController/captcha')->name('captcha');
    //图形验证码
    Route::get('ajcaptcha', 'v1.LoginController/ajcaptcha')->name('ajcaptcha');
    //图形验证码
    Route::post('ajcheck', 'v1.LoginController/ajcheck')->name('ajcheck');
    //验证码发送
    Route::post('register/verify', 'v1.LoginController/verify')->name('registerVerify');
    //手机号注册
    Route::post('register', 'v1.LoginController/register')->name('register');
    //手机号修改密码
    Route::post('register/reset', 'v1.LoginController/reset')->name('registerReset');
    // 绑定手机号(静默授权 还未有用户信息)
    Route::post('binding', 'v1.LoginController/binding_phone')->name('bindingPhone');
    // 支付宝复制链接支付
    Route::get('ali_pay', 'v1.order.StoreOrderController/aliPay')->name('aliPay');
    //查询版权
    Route::get('copyright', 'v1.PublicController/copyright')->option(['real_name' => '申请版权']);

})->middleware(\app\http\middleware\AllowOriginMiddleware::class)->middleware(\app\api\middleware\StationOpenMiddleware::class);


//管理员订单操作类
Route::group(function () {
    Route::get('admin/order/statistics', 'v1.admin.StoreOrderController/statistics')->name('adminOrderStatistics');//订单数据统计
    Route::get('admin/order/data', 'v1.admin.StoreOrderController/data')->name('adminOrderData');//订单每月统计数据
    Route::get('admin/order/list', 'v1.admin.StoreOrderController/lst')->name('adminOrderList');//订单列表
    Route::get('admin/refund_order/list', 'v1.admin.StoreOrderController/refundOrderList')->name('adminOrderRefundList');//退款订单列表
    Route::get('admin/order/detail/:orderId', 'v1.admin.StoreOrderController/detail')->name('adminOrderDetail');//订单详情
    Route::get('admin/refund_order/detail/:uni', 'v1.admin.StoreOrderController/refundOrderDetail')->name('RefundOrderDetail');//退款订单详情
    Route::get('admin/order/delivery/gain/:orderId', 'v1.admin.StoreOrderController/delivery_gain')->name('adminOrderDeliveryGain');//订单发货获取订单信息
    Route::post('admin/order/delivery/keep/:id', 'v1.admin.StoreOrderController/delivery_keep')->name('adminOrderDeliveryKeep');//订单发货
    Route::post('admin/order/price', 'v1.admin.StoreOrderController/price')->name('adminOrderPrice');//订单改价
    Route::post('admin/order/remark', 'v1.admin.StoreOrderController/remark')->name('adminOrderRemark');//订单备注
    Route::post('admin/order/agreeExpress', 'v1.admin.StoreOrderController/agreeExpress')->name('adminOrderAgreeExpress');//订单同意退货
    Route::post('admin/refund_order/remark', 'v1.admin.StoreOrderController/refundRemark')->name('refundRemark');//退款订单备注
    Route::get('admin/order/time', 'v1.admin.StoreOrderController/time')->name('adminOrderTime');//订单交易额时间统计
    Route::post('admin/order/offline', 'v1.admin.StoreOrderController/offline')->name('adminOrderOffline');//订单支付
    Route::post('admin/order/refund', 'v1.admin.StoreOrderController/refund')->name('adminOrderRefund');//订单退款
    Route::post('order/order_verific', 'v1.admin.StoreOrderController/order_verific')->name('order');//订单核销
    Route::get('admin/order/delivery', 'v1.admin.StoreOrderController/getDeliveryAll')->name('getDeliveryAll');//获取配送员
    Route::get('admin/order/delivery_info', 'v1.admin.StoreOrderController/getDeliveryInfo')->name('getDeliveryInfo');//获取电子面单默认信息
    Route::get('admin/order/export_temp', 'v1.admin.StoreOrderController/getExportTemp')->name('getExportTemp');//获取电子面单模板获取
    Route::get('admin/order/export_all', 'v1.admin.StoreOrderController/getExportAll')->name('getExportAll');//获取物流公司
})->middleware(\app\http\middleware\AllowOriginMiddleware::class)->middleware(\app\api\middleware\StationOpenMiddleware::class)->middleware(\app\api\middleware\AuthTokenMiddleware::class, true)->middleware(\app\api\middleware\CustomerMiddleware::class);

//会员授权接口
Route::group(function () {
    //获取支付方式
    Route::get('pay/config', 'v1.PayController/config')->name('payConfig');
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


    Route::get('rank', 'v1.user.UserController/rank')->name('rank');//推广人排行
    //用戶类 分享
    Route::post('user/share', 'v1.PublicController/user_share')->name('user_share');//记录用户分享
    Route::get('user/share/words', 'v1.PublicController/copy_share_words')->name('user_share_words');//关键字分享
    //用户类 点赞
//    Route::post('like/add', 'user.UserController/like_add')->name('likeAdd');//添加点赞
//    Route::post('like/del', 'user.UserController/like_del')->name('likeDel');//取消点赞
    //用户类 签到
    Route::get('sign/config', 'v1.user.UserSignController/sign_config')->name('signConfig');//签到配置
    Route::get('sign/list', 'v1.user.UserSignController/sign_list')->name('signList');//签到列表
    Route::get('sign/month', 'v1.user.UserSignController/sign_month')->name('signIntegral');//签到列表（年月）
    Route::post('sign/user', 'v1.user.UserSignController/sign_user')->name('signUser');//签到用户信息
    Route::post('sign/integral', 'v1.user.UserSignController/sign_integral')->name('signIntegral')->middleware(BlockerMiddleware::class);//签到
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
    Route::post('order/check_shipping', 'v1.order.StoreOrderController/checkShipping')->name('checkShipping'); //检测是否显示快递和自提标签
    Route::post('order/confirm', 'v1.order.StoreOrderController/confirm')->name('orderConfirm'); //订单确认
    Route::post('order/computed/:key', 'v1.order.StoreOrderController/computedOrder')->name('computedOrder'); //计算订单金额
    Route::post('order/create/:key', 'v1.order.StoreOrderController/create')->name('orderCreate')->middleware(BlockerMiddleware::class); //订单创建
    Route::get('order/data', 'v1.order.StoreOrderController/data')->name('orderData'); //订单统计数据
    Route::get('order/list', 'v1.order.StoreOrderController/lst')->name('orderList'); //订单列表
    Route::get('order/detail/:uni/[:cartId]', 'v1.order.StoreOrderController/detail')->name('orderDetail'); //订单详情
    Route::get('order/refund_detail/:uni/[:cartId]', 'v1.order.StoreOrderController/refund_detail')->name('refundDetail'); //退款订单详情
    Route::get('order/refund/reason', 'v1.order.StoreOrderController/refund_reason')->name('orderRefundReason')->middleware(BlockerMiddleware::class); //订单退款理由
    Route::post('order/refund/verify', 'v1.order.StoreOrderController/refund_verify')->name('orderRefundVerify')->middleware(BlockerMiddleware::class); //订单退款审核
    Route::post('order/take', 'v1.order.StoreOrderController/take')->name('orderTake')->middleware(BlockerMiddleware::class); //订单收货
    Route::get('order/express/:uni/[:type]', 'v1.order.StoreOrderController/express')->name('orderExpress'); //订单查看物流
    Route::post('order/del', 'v1.order.StoreOrderController/del')->name('orderDel'); //订单删除
    Route::post('order/again', 'v1.order.StoreOrderController/again')->name('orderAgain'); //订单 再次下单
    Route::post('order/pay', 'v1.order.StoreOrderController/pay')->name('orderPay'); //订单支付
    Route::post('order/product', 'v1.order.StoreOrderController/product')->name('orderProduct'); //订单商品信息
    Route::post('order/comment', 'v1.order.StoreOrderController/comment')->name('orderComment'); //订单评价
    //活动---砍价
    Route::get('bargain/detail/:id', 'v1.activity.StoreBargainController/detail')->name('bargainDetail');//砍价商品详情
    Route::post('bargain/start', 'v1.activity.StoreBargainController/start')->name('bargainStart');//砍价开启
    Route::post('bargain/start/user', 'v1.activity.StoreBargainController/start_user')->name('bargainStartUser');//砍价 开启砍价用户信息
    Route::post('bargain/share', 'v1.activity.StoreBargainController/share')->name('bargainShare');//砍价 观看/分享/参与次数
    Route::post('bargain/help', 'v1.activity.StoreBargainController/help')->name('bargainHelp');//砍价 帮助好友砍价
    Route::post('bargain/help/price', 'v1.activity.StoreBargainController/help_price')->name('bargainHelpPrice');//砍价 砍掉金额
    Route::post('bargain/help/count', 'v1.activity.StoreBargainController/help_count')->name('bargainHelpCount');//砍价 砍价帮总人数、剩余金额、进度条、已经砍掉的价格
    Route::post('bargain/help/list', 'v1.activity.StoreBargainController/help_list')->name('bargainHelpList');//砍价 砍价帮
    Route::post('bargain/poster', 'v1.activity.StoreBargainController/poster')->name('bargainPoster');//砍价海报
    Route::get('bargain/user/list', 'v1.activity.StoreBargainController/user_list')->name('bargainUserList');//砍价列表(已参与)
    Route::post('bargain/user/cancel', 'v1.activity.StoreBargainController/user_cancel')->name('bargainUserCancel');//砍价取消
    Route::get('bargain/poster_info/:bargainId', 'v1.activity.StoreBargainController/posterInfo')->name('posterInfo');//砍价海报详细信息
    //活动---拼团
    Route::get('combination/pink/:id', 'v1.activity.StoreCombinationController/pink')->name('combinationPink');//拼团开团
    Route::post('combination/remove', 'v1.activity.StoreCombinationController/remove')->name('combinationRemove');//拼团 取消开团
    Route::post('combination/poster', 'v1.activity.StoreCombinationController/poster')->name('combinationPoster');//拼团海报
    Route::get('combination/poster_info/:id', 'v1.activity.StoreCombinationController/posterInfo')->name('pinkPosterInfo');//拼团海报详细获取
    //账单类
    Route::post('spread/people', 'v1.user.UserController/spread_people')->name('spreadPeople');//推荐用户
    Route::post('spread/order', 'v1.user.UserBillController/spread_order')->name('spreadOrder');//推广订单
    Route::get('spread/commission/:type', 'v1.user.UserBillController/spread_commission')->name('spreadCommission');//推广佣金明细
    Route::get('spread/count/:type', 'v1.user.UserBillController/spread_count')->name('spreadCount');//推广 佣金 3/提现 4 总和
    Route::get('spread/banner', 'v1.user.UserBillController/spread_banner')->name('spreadBanner');//推广分销二维码海报生成
    Route::get('integral/list', 'v1.user.UserBillController/integral_list')->name('integralList');//积分记录
    Route::get('user/routine_code', 'v1.user.UserBillController/getRoutineCode')->name('getRoutineCode');//小程序二维码
    Route::get('user/spread_info', 'v1.user.UserBillController/getSpreadInfo')->name('getSpreadInfo');//获取分销背景等信息
    Route::post('division/order', 'v1.user.UserBillController/divisionOrder')->name('divisionOrder');//事业部推广订单
    //提现类
    Route::get('extract/bank', 'v1.user.UserExtractController/bank')->name('extractBank');//提现银行/提现最低金额
    Route::post('extract/cash', 'v1.user.UserExtractController/cash')->name('extractCash');//提现申请
    //充值类
    Route::post('recharge/recharge', 'v1.user.UserRechargeController/recharge')->name('rechargeRecharge');//统一充值
    Route::post('recharge/routine', 'v1.user.UserRechargeController/routine')->name('rechargeRoutine');//小程序充值
    Route::post('recharge/wechat', 'v1.user.UserRechargeController/wechat')->name('rechargeWechat');//公众号充值
    Route::get('recharge/index', 'v1.user.UserRechargeController/index')->name('rechargeQuota');//充值余额选择
    //会员等级类
    Route::get('user/level/detection', 'v1.user.UserLevelController/detection')->name('userLevelDetection');//检测用户是否可以成为会员
    Route::get('user/level/grade', 'v1.user.UserLevelController/grade')->name('userLevelGrade');//会员等级列表
    Route::get('user/level/task/:id', 'v1.user.UserLevelController/task')->name('userLevelTask');//获取等级任务
    Route::get('user/level/info', 'v1.user.UserLevelController/userLevelInfo')->name('levelInfo');//获取等级任务
    Route::get('user/level/expList', 'v1.user.UserLevelController/expList')->name('expList');//获取等级任务
    Route::get('user/record', 'v1.user.StoreService/recordList')->name('recordList');//获取用户和客服的消息列表

    //首页获取未支付订单
    Route::get('order/nopay', 'v1.order.StoreOrderController/get_noPay')->name('getNoPay');//获取未支付订单

    Route::get('seckill/code/:id', 'v1.activity.StoreSeckillController/code')->name('seckillCode');//秒杀商品海报
    Route::get('combination/code/:id', 'v1.activity.StoreCombinationController/code')->name('combinationCode');//拼团商品海报

    //会员卡
    Route::get('user/member/card/index', 'v1.user.MemberCardController/index')->name('userMemberCardIndex');// 主页会员权益介绍页
    Route::post('user/member/card/draw', 'v1.user.MemberCardController/draw_member_card')->name('userMemberCardDraw');//卡密领取会员卡
    Route::post('user/member/card/create', 'v1.order.OtherOrderController/create')->name('userMemberCardCreate');//购买卡创建订单
    Route::get('user/member/coupons/list', 'v1.user.MemberCardController/memberCouponList')->name('userMemberCouponsList');//会员券列表
    Route::get('user/member/overdue/time', 'v1.user.MemberCardController/getOverdueTime')->name('userMemberOverdueTime');//会员时间
    //线下付款
    Route::post('order/offline/check/price', 'v1.order.OtherOrderController/computed_offline_pay_price')->name('orderOfflineCheckPrice'); //检测线下付款金额
    Route::post('order/offline/create', 'v1.order.OtherOrderController/create')->name('orderOfflineCreate'); //检测线下付款金额
    Route::get('order/offline/pay/type', 'v1.order.OtherOrderController/pay_type')->name('orderOfflineCreate'); //线下付款支付方式
    //消息站内信
    Route::get('user/message_system/list', 'v1.user.MessageSystemController/message_list')->name('MessageSystemList'); //站内信列表
    Route::get('user/message_system/detail/:id', 'v1.user.MessageSystemController/detail')->name('MessageSystemDetail'); //详情
    Route::get('user/message_system/edit_message', 'v1.user.MessageSystemController/edit_message')->name('EditMessage');//站内信设为未读/删除

    //积分商城订单
    Route::post('store_integral/order/confirm', 'v1.order.StoreIntegralOrderController/confirm')->name('storeIntegralOrderConfirm'); //订单确认
    Route::post('store_integral/order/create', 'v1.order.StoreIntegralOrderController/create')->name('storeIntegralOrderCreate'); //订单创建
    Route::get('store_integral/order/detail/:uni', 'v1.order.StoreIntegralOrderController/detail')->name('storeIntegralOrderDetail'); //订单详情
    Route::get('store_integral/order/list', 'v1.order.StoreIntegralOrderController/lst')->name('storeIntegralOrderList'); //订单列表
    Route::post('store_integral/order/take', 'v1.order.StoreIntegralOrderController/take')->name('storeIntegralOrderTake'); //订单收货
    Route::get('store_integral/order/express/:uni', 'v1.order.StoreIntegralOrderController/express')->name('storeIntegralOrderExpress'); //订单查看物流
    Route::post('store_integral/order/del', 'v1.order.StoreIntegralOrderController/del')->name('storeIntegralOrderDel'); //订单删除

    /** 好友代付 */
    Route::get('order/friend_detail', 'v1.order.StoreOrderController/friendDetail')->name('friendDetail');//代付详情

    /** 佣金相关 */
    Route::get('commission', 'v1.user.UserBrokerageController/commission')->name('commission');//推广数据 昨天的佣金 累计提现金额 当前佣金
    Route::get('brokerage_rank', 'v1.user.UserBrokerageController/brokerageRank')->name('brokerageRank');//佣金排行

    /** 退款相关 */
    Route::get('order/refund/cart_info/:id', 'v1.order.StoreOrderController/refundCartInfo')->name('refundCartInfo');//退款中间页面订单商品列表
    Route::post('order/refund/cart_info', 'v1.order.StoreOrderController/refundCartInfoList')->name('StoreOrderRefundCartInfoList');//获取退款商品列表
    Route::post('order/refund/apply/:id', 'v1.order.StoreOrderController/applyRefund')->name('StoreOrderApplyRefund');//订单申请退款
    Route::get('order/refund/list', 'v1.order.StoreOrderRefundController/refundList')->name('refundList');//退款单列表
    Route::get('order/refund/detail/:uni', 'v1.order.StoreOrderRefundController/refundDetail')->name('refundDetail');//退款单详情
    Route::post('order/refund/cancel/:uni', 'v1.order.StoreOrderRefundController/cancelApply')->name('cancelApply');//用户取消退款申请
    Route::post('order/refund/express', 'v1.order.StoreOrderRefundController/applyExpress')->name('refundDetail');//退款单详情
    Route::get('order/refund/del/:uni', 'v1.order.StoreOrderRefundController/delRefund')->name('delRefund');//用户取消退款申请

    /** 代理商相关 */
    Route::get('agent/apply/info', 'v1.user.DivisionController/applyInfo')->name('申请详情');//申请详情
    Route::post('agent/apply/:id', 'v1.user.DivisionController/applyAgent')->name('applyAgent');//申请代理商
    Route::get('agent/get_agent_agreement', 'v1.user.DivisionController/getAgentAgreement')->name('getAgentAgreement');//代理商规则
    Route::get('agent/get_staff_list', 'v1.user.DivisionController/getStaffList')->name('getStaffList');//员工列表
    Route::post('agent/set_staff_percent', 'v1.user.DivisionController/setStaffPercent')->name('setStaffPercent');//设置员工分佣比例
    Route::get('agent/del_staff/:uid', 'v1.user.DivisionController/delStaff')->name('delStaff');//删除员工

    /** 用户注销 */

    Route::get('user_cancel', 'v1.user.UserController/SetUserCancel')->name('SetUserCancel');//用户注销

    /** 用户浏览记录 */
    Route::get('user/visit_list', 'v1.user.UserController/visitList')->name('visitList');//商品浏览列表
    Route::delete('user/visit', 'v1.user.UserController/visitDelete')->name('visitDelete');//商品浏览记录删除


})->middleware(\app\http\middleware\AllowOriginMiddleware::class)->middleware(\app\api\middleware\StationOpenMiddleware::class)->middleware(\app\api\middleware\AuthTokenMiddleware::class, true);
//未授权接口
Route::group(function () {
    Route::get('menu/user', 'v1.PublicController/menu_user')->name('menuUser');//个人中心菜单
    //公共类
    Route::get('index', 'v1.PublicController/index')->name('index');//首页
    Route::get('site_config', 'v1.PublicController/getSiteConfig')->name('getSiteConfig');//获取网站配置
    //DIY接口
    Route::get('diy/get_diy/[:id]', 'v1.PublicController/getDiy');
    Route::get('home/products', 'v1.PublicController/home_products_list')->name('homeProductsList');//获取首页推荐不同类型商品的轮播图和商品

    Route::get('search/keyword', 'v1.PublicController/search')->name('searchKeyword');//热门搜索关键字获取
    //商品分类类
    Route::get('category', 'v1.store.CategoryController/category')->name('category');
    Route::get('category_version', 'v1.store.CategoryController/getCategoryVersion')->name('getCategoryVersion');//商品分类类版本
    //商品类
    Route::post('image_base64', 'v1.PublicController/get_image_base64')->name('getImageBase64');// 获取图片base64
    Route::get('product/detail/:id/[:type]', 'v1.store.StoreProductController/detail')->name('detail');//商品详情
    Route::get('groom/list/:type', 'v1.store.StoreProductController/groom_list')->name('groomList');//获取首页推荐不同类型商品的轮播图和商品
    Route::get('products', 'v1.store.StoreProductController/lst')->name('products');//商品列表
    Route::get('product/hot', 'v1.store.StoreProductController/product_hot')->name('productHot');//为你推荐
    Route::get('reply/list/:id', 'v1.store.StoreProductController/reply_list')->name('replyList');//商品评价列表
    Route::get('reply/config/:id', 'v1.store.StoreProductController/reply_config')->name('replyConfig');//商品评价数量和好评度
    Route::get('advance/list', 'v1.store.StoreProductController/advanceList')->name('advanceList');//预售商品列表

    //文章分类类
    Route::get('article/category/list', 'v1.publics.ArticleCategoryController/lst')->name('articleCategoryList');//文章分类列表
    //文章类
    Route::get('article/list/:cid', 'v1.publics.ArticleController/lst')->name('articleList');//文章列表
    Route::get('article/details/:id', 'v1.publics.ArticleController/details')->name('articleDetails');//文章详情
    Route::get('article/hot/list', 'v1.publics.ArticleController/hot')->name('articleHotList');//文章 热门
    Route::get('article/new/list', 'v1.publics.ArticleController/new')->name('articleNewList');//文章 最新
    Route::get('article/banner/list', 'v1.publics.ArticleController/banner')->name('articleBannerList');//文章 banner
    //活动---秒杀
    Route::get('seckill/index', 'v1.activity.StoreSeckillController/index')->name('seckillIndex');//秒杀商品时间区间
    Route::get('seckill/list/:time', 'v1.activity.StoreSeckillController/lst')->name('seckillList');//秒杀商品列表
    Route::get('seckill/detail/:id/[:time]', 'v1.activity.StoreSeckillController/detail')->name('seckillDetail');//秒杀商品详情
    //活动---砍价
    Route::get('bargain/config', 'v1.activity.StoreBargainController/config')->name('bargainConfig');//砍价商品列表配置
    Route::get('bargain/list', 'v1.activity.StoreBargainController/lst')->name('bargainList');//砍价商品列表
    //活动---拼团
    Route::get('combination/list', 'v1.activity.StoreCombinationController/lst')->name('combinationList');//拼团商品列表
    Route::get('combination/banner_list', 'v1.activity.StoreCombinationController/banner_list')->name('banner_list');//拼团商品列表
    Route::get('combination/detail/:id', 'v1.activity.StoreCombinationController/detail')->name('combinationDetail');//拼团商品详情
    //活动-预售
    Route::get('advance/detail/:id', 'v1.activity.StoreAdvanceController/detail')->name('advanceDetail');//预售商品详情

    //用户类
    Route::get('user/activity', 'v1.user.UserController/activity')->name('userActivity');//活动状态

    //微信
    Route::get('wechat/config', 'v1.wechat.WechatController/config')->name('wechatConfig');//微信 sdk 配置
    Route::get('wechat/auth', 'v1.wechat.WechatController/auth')->name('wechatAuth');//微信授权
    Route::post('wechat/app_auth', 'v1.wechat.WechatController/appAuth')->name('appAuth');//微信APP授权

    //小程序登陆
    Route::post('wechat/mp_auth', 'v1.wechat.AuthController/mp_auth')->name('mpAuth');//小程序登陆
    Route::get('wechat/get_logo', 'v1.wechat.AuthController/get_logo')->name('getLogo');//小程序登陆授权展示logo
    Route::get('wechat/temp_ids', 'v1.wechat.AuthController/temp_ids')->name('wechatTempIds');//小程序订阅消息
    Route::get('wechat/live', 'v1.wechat.AuthController/live')->name('wechatLive');//小程序直播列表
    Route::get('wechat/livePlaybacks/:id', 'v1.wechat.AuthController/livePlaybacks')->name('livePlaybacks');//小程序直播回放

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
    //用户是否关注
    Route::get('subscribe', 'v1.user.UserController/subscribe')->name('Subscribe');
    //门店列表
    Route::get('store_list', 'v1.PublicController/store_list')->name('storeList');
    //获取城市列表
    Route::get('city_list', 'v1.PublicController/city_list')->name('cityList');
    //拼团数据
    Route::get('pink', 'v1.PublicController/pink')->name('pinkData');
    //获取底部导航
    Route::get('navigation/[:template_name]', 'v1.PublicController/getNavigation')->name('getNavigation');
    //用户访问
    Route::post('user/set_visit', 'v1.user.UserController/set_visit')->name('setVisit');// 添加用户访问记录
    //复制口令接口
    Route::get('copy_words', 'v1.PublicController/copy_words')->name('copyWords');// 复制口令接口
    //获取网站配置
    Route::get('site_config', 'v1.PublicController/getSiteConfig')->name('getSiteConfig');//获取网站配置

    //活动---积分商城
    Route::get('store_integral/index', 'v1.activity.StoreIntegralController/index')->name('storeIntegralIndex');//积分商城首页数据
    Route::get('store_integral/list', 'v1.activity.StoreIntegralController/lst')->name('storeIntegralList');//积分商品列表
    Route::get('store_integral/detail/:id', 'v1.activity.StoreIntegralController/detail')->name('storeIntegralDetail');//积分商品详情

    //获取app最新版本
    Route::get('get_new_app/:platform', 'v1.PublicController/getNewAppVersion')->name('getNewAppVersion');//获取app最新版本
    //获取客服类型
    Route::get('get_customer_type', 'v1.PublicController/getCustomerType')->name('getCustomerType');//获取客服类型
    //长链接设置
    Route::get('get_workerman_url', 'v1.PublicController/getWorkerManUrl')->name('getWorkerManUrl');
    //首页开屏广告
    Route::get('get_open_adv', 'v1.PublicController/getOpenAdv')->name('getOpenAdv');
    //获取用户协议
    Route::get('user_agreement', 'v1.PublicController/getUserAgreement')->name('getUserAgreement');
    Route::get('get_agreement/:type', 'v1.PublicController/getAgreement')->name('getAgreement');

    //获取多语言类型列表
    Route::get('get_lang_type_list', 'v1.PublicController/getLangTypeList')->name('getLangTypeList');
    //获取当前语言json
    Route::get('get_lang_json', 'v1.PublicController/getLangJson')->name('getLangJson');
    //获取当前后台设置的默认语言类型
    Route::get('get_default_lang_type', 'v1.PublicController/getDefaultLangType')->name('getLangJson');

    /** 定时任务接口 */
    //检测定时任务接口
    Route::get('timer/check', 'v1.TimerController/timerCheck')->name('timerCheck');
    //未支付自动取消订单
    Route::get('timer/order_cancel', 'v1.TimerController/orderUnpaidCancel')->name('orderUnpaidCancel');
    //拼团到期订单处理
    Route::get('timer/pink_expiration', 'v1.TimerController/pinkExpiration')->name('pinkExpiration');
    //自动解绑上级绑定
    Route::get('timer/agent_unbind', 'v1.TimerController/agentUnbind')->name('agentUnbind');
    //更新直播商品状态
    Route::get('timer/live_product_status', 'v1.TimerController/syncGoodStatus')->name('syncGoodStatus');
    //更新直播间状态
    Route::get('timer/live_room_status', 'v1.TimerController/syncRoomStatus')->name('syncRoomStatus');
    //自动收货
    Route::get('timer/take_delivery', 'v1.TimerController/autoTakeOrder')->name('autoTakeOrder');
    //查询预售到期商品自动下架
    Route::get('timer/advance_off', 'v1.TimerController/downAdvance')->name('downAdvance');
    //自动好评
    Route::get('timer/product_replay', 'v1.TimerController/autoComment')->name('autoComment');
    //清除昨日海报
    Route::get('timer/clear_poster', 'v1.TimerController/emptyYesterdayAttachment')->name('emptyYesterdayAttachment');


})->middleware(\app\http\middleware\AllowOriginMiddleware::class)->middleware(\app\api\middleware\StationOpenMiddleware::class)->middleware(\app\api\middleware\AuthTokenMiddleware::class, false);

Route::miss(function () {
    if (app()->request->isOptions()) {
        $header = Config::get('cookie.header');
        unset($header['Access-Control-Allow-Credentials']);
        return Response::create('ok')->code(200)->header($header);
    } else
        return Response::create()->code(404);
});
