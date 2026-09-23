// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

import {
	SUBSCRIBE_MESSAGE
} from '../config/cache.js';

let refreshPromise = null;

/**
 * 刷新小程序订阅模板 ID。
 * @param {Function} fetchIds 获取模板 ID 的请求函数
 * @return {Promise<object|null>}
 */
export function refreshSubscribeTemplateIds(fetchIds) {
	if (refreshPromise) return refreshPromise;

	refreshPromise = Promise.resolve()
		.then(() => fetchIds())
		.then((res) => {
			if (res?.status === 200 && res.data && typeof res.data === 'object' && !Array.isArray(res.data)) {
				uni.setStorageSync(SUBSCRIBE_MESSAGE, JSON.stringify(res.data));
			}
			return res;
		})
		.catch(() => null)
		.finally(() => {
			refreshPromise = null;
		});

	return refreshPromise;
}

export function auth() {
	const messageTmplIds = uni.getStorageSync(SUBSCRIBE_MESSAGE);
	if (!messageTmplIds) return {};

	try {
		const tmplIds = JSON.parse(messageTmplIds);
		return tmplIds && typeof tmplIds === 'object' && !Array.isArray(tmplIds) ? tmplIds : {};
	} catch (error) {
		return {};
	}
}

export function openOrderCreateSubscribe() {
    let tmplIds = auth();
    return subscribe([
        tmplIds.order_create,
    ]);
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
		tmplIds.order_refund,
		tmplIds.send_order_refund_no_status,
	]);
}

/**
 * 售后申请后可能产生的退款结果通知
 * 同时订阅退款成功和退款拒绝，覆盖后台实际发送的两条用户通知。
 */
export function openAfterSaleSubscribe() {
	return openOrderRefundSubscribe();
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
export function subscribe(tmplIds) {
	const validTmplIds = Array.isArray(tmplIds)
		? tmplIds.filter((tmplId) => typeof tmplId === 'string' && tmplId.trim() !== '')
		: [];
    console.log(validTmplIds)
	return new Promise((resolve) => {
		if (!validTmplIds.length) {
			return resolve({ errMsg: 'requestSubscribeMessage:ok' });
		}

		// #ifdef MP-WEIXIN
		wx.requestSubscribeMessage({
			tmplIds: validTmplIds,
			success(res) {
				return resolve(res);
			},
			fail(res) {
				return resolve(res);
			}
		});
		// #endif
		// #ifndef MP-WEIXIN
		// 非微信小程序平台无此接口，保持 Promise 语义不阻塞调用方
		resolve({ errMsg: 'requestSubscribeMessage:fail platform not supported' });
		// #endif
	});
}
