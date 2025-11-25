// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2024 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

import {
	SUBSCRIBE_MESSAGE
} from '../config/cache.js';

export function auth() {
	let tmplIds = {};
	let messageTmplIds = uni.getStorageSync(SUBSCRIBE_MESSAGE);
	tmplIds = messageTmplIds ? JSON.parse(messageTmplIds) : {};
	return tmplIds;
}

/**
 * 支付成功后订阅消息id
 * 订阅  确认收货通知 订单支付成功  新订单管理员提醒 
 */
export function openPaySubscribe() {
	let tmplIds = auth();
	return subscribe([
		tmplIds.order_pay_success,
		tmplIds.order_deliver_success,
		tmplIds.order_postage_success,
	]);
}

/**
 * 订单相关订阅消息
 * 送货 发货 取消订单
 */
export function openOrderSubscribe() {
	let tmplIds = auth();
	return subscribe([
		tmplIds.order_take,
		tmplIds.integral_accout
	]);
}

/**
 * 提现消息订阅
 * 成功 和 失败 消息
 */
export function openExtrctSubscribe() {
	let tmplIds = auth();
	return subscribe([
		tmplIds.user_extract
	]);
}

/**
 * 拼团成功
 */
export function openPinkSubscribe() {
	let tmplIds = auth();
	return subscribe([
		tmplIds.order_user_groups_success
	]);
}

/**
 * 砍价成功
 */
export function openBargainSubscribe() {
	let tmplIds = auth();
	return subscribe([
		tmplIds.bargain_success
	]);
}

/**
 * 订单退款
 */
export function openOrderRefundSubscribe() {
	let tmplIds = auth();
	return subscribe([
		tmplIds.order_refund
	]);
}

/**
 * 充值成功
 */
export function openRechargeSubscribe() {
	let tmplIds = auth();
	return subscribe([
		tmplIds.recharge_success
	]);
}

/**
 * 提现成功
 */
export function openRevenueSubscribe() {
	let tmplIds = auth();
	return subscribe([
		tmplIds.revenue_received
	]);
}

/**
 * 调起订阅界面
 * array tmplIds 模板id
 */
export function subscribe(subscrip443tionmessagee502call) {
	 let weChat = wx;
	return new Promise((reslove, reject) => {
		weChat.requestSubscribeMessage({
			tmplIds: subscrip443tionmessagee502call,
			success(res) {
				return reslove(res);
			},
			fail(res) {
				return reslove(res);
			}
		})
	});
}
