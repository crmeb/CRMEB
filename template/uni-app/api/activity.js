// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2024 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

import request from "@/utils/request.js";
/**
 * 
 * 所有活动接口 包括：拼团，砍价，秒杀
 * 
 */

/**
 * 拼团列表
 * 
 */
export function getCombinationList(data) {
	return request.get('combination/list', data, {
		noAuth: true
	});
}

/**
 * 拼团详情
 * 
 */
export function getCombinationDetail(id) {
	return request.get('combination/detail/' + id);
}

/**
 * 拼团 开团
 */
export function getCombinationPink(id) {
	return request.get("combination/pink/" + id);
}

/**
 * 拼团 取消开团
 */
export function postCombinationRemove(data) {
	return request.post("combination/remove", data);
}

/**
 * 砍价列表
 */
export function getBargainList(data) {
	return request.get("bargain/list", data, {
		noAuth: true
	});
}

/**
 * 拼团轮播
 * 
 */
export function getCombinationBannerList(data) {
	return request.get('combination/banner_list', data, {
		noAuth: true
	});
}

/**
 * 拼团人数
 * 
 */
export function getPink(data) {
	return request.get('pink', data, {
		noAuth: true
	});
}

/**
 * 
 * 砍价列表(已参与)
 * @param object data
 */
export function getBargainUserList(data) {
	return request.get('bargain/user/list', data);
}


/**
 * 砍价产品详情
 */
export function getBargainDetail(id, uid) {
	return request.get(`bargain/detail/${id}?bargainUid=${uid}`);
}

/**
 * 砍价 开启砍价用户信息
 */
export function postBargainStartUser(data) {
	return request.post("bargain/start/user", data);
}

/**
 * 砍价开启
 */
export function postBargainStart(bargainId) {
	return request.post("bargain/start", {
		bargainId: bargainId
	});
}

/**
 * 砍价 帮助好友砍价
 */
export function postBargainHelp(data) {
	return request.post("bargain/help", data);
}

/**
 * 砍价 砍掉金额
 */
export function postBargainHelpPrice(data) {
	return request.post("bargain/help/price", data);
}

/**
 * 砍价 砍价帮
 */
export function postBargainHelpList(data) {
	return request.post("bargain/help/list", data);
}

/**
 * 砍价 砍价帮总人数、剩余金额、进度条、已经砍掉的价格
 */
export function postBargainHelpCount(data) {
	return request.post("bargain/help/count", data);
}

/**
 * 砍价 观看/分享/参与次数
 */
export function postBargainShare(bargainId) {
	return request.post("bargain/share", {
		bargainId: bargainId
	});
}

/**
 * 秒杀产品时间区间
 * 
 */
export function getSeckillIndexTime() {
	return request.get('seckill/index', {}, {
		noAuth: true
	});
}

/**
 * 秒杀产品列表
 * @param int time
 * @param object data
 */
export function getSeckillList(time, data) {
	return request.get('seckill/list/' + time, data, {
		noAuth: true
	});
}

/**
 * 秒杀产品详情
 * @param int id
 */
export function getSeckillDetail(id, data) {
	return request.get(`seckill/detail/${id}`, data);
}

/**
 * 砍价海报
 * @param object data
 * 
 */
export function getBargainPoster(data) {
	return request.post('bargain/poster', data)
}

/**
 * 拼团海报
 * @param object data
 * 
 */
export function getCombinationPoster(data) {
	return request.post('combination/poster', data)
}

/**
 * 砍价取消
 */
export function getBargainUserCancel(data) {
	return request.post("bargain/user/cancel", data);
}

/**
 * 获取秒杀小程序二维码
 */
export function seckillCode(id, data) {
	return request.get("seckill/code/" + id, data);
}

/**
 * 获取拼团小程序二维码
 */
export function scombinationCode(id) {
	return request.get("combination/code/" + id);
}

/**
 * 获取砍价海报详细信息
 */
export function getCombinationPosterData(id) {
	return request.get("combination/poster_info/" + id);
}


/**
 * 获取砍价海报详细信息
 */
export function getBargainPosterData(id) {
	return request.get("bargain/poster_info/" + id);
}

/**
 * 获取积分订单详细信息
 */
export function integralOrderConfirm(data) {
	return request.post('store_integral/order/confirm', data);
}

/**
 * 获取积分订单创建
 */
export function integralOrderCreate(data) {
	return request.post('store_integral/order/create', data);
}
/**
 * 获取积分订单详情
 * @param string cartId
 */
export function integralOrderDetails(order) {
	return request.get(`store_integral/order/detail/${order}`);
}

/**
 * 积分产品详情
 * @param int id
 * 
 */
export function getIntegralProductDetail(id) {
	return request.get('store_integral/detail/' + id, {}, {
		noAuth: true
	});
}

/**
 * 积分商城商品列表
 * @param object data
 */
export function getStoreIntegralList(data) {
	return request.get('store_integral/list', data, {
		noAuth: true
	});
}

/**
 * 积分兑换列表
 * @param object data
 */
export function getIntegralOrderList(data) {
	return request.get('store_integral/order/list', data);
}

/**
 * 积分兑换详情
 */
export function getLogisticsDetails(orderId) {
	return request.get(`store_integral/order/express/${orderId}`);
}

/**
 * 积分兑换订单确认收货
 * @param object data
 */
export function orderTake(data) {
	return request.post(`store_integral/order/take`, data);
}

/**
 * 积分兑换订单删除
 * @param object data
 */
export function orderDel(data) {
	return request.post(`store_integral/order/del`, data);
}

/**
 * 预售商品列表
 */
export function getPresellList(data) {
	return request.get("advance/list", data);
}