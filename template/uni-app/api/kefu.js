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
 * 客服登录
 * @param data object 用户账号密码
 */
export function kefuLogin(data) {
	return request.post("login", data, {
		noAuth: true,
		kefu: true
	});
}

/**
 * 获取左侧客服聊天用户列表
 * @constructor
 */
export function record(data) {
	return request.get("user/record", data, {
		noAuth: true,
		kefu: true
	});
}

/**
 * 客服话术
 * @constructor
 */
export function speeChcraft(data) {
	return request.get("service/speechcraft", data, {
		noAuth: true,
		kefu: true
	});
}

/**
 * 客服转接列表
 * @constructor
 */
export function transferList(data) {
	return request.get("service/transfer_list", data, {
		noAuth: true,
		kefu: true
	});
}

/**
 * 商品购买记录
 * @constructor
 */
export function productCart(id, data) {
	return request.get("product/cart/" + id, data, {
		noAuth: true,
		kefu: true
	});
}

/**
 * 热销商品
 * @constructor
 */
export function productHot(id, data) {
	return request.get("product/hot/" + id, data, {
		noAuth: true,
		kefu: true
	});
}

/**
 * 商品足记
 * @constructor
 */
export function productVisit(id, data) {
	return request.get("product/visit/" + id, data, {
		noAuth: true,
		kefu: true
	});
}

/**
 * 客服用户聊天列表
 * @constructor
 */
export function serviceList(data) {
	return request.get("service/list", data, {
		noAuth: true,
		kefu: true
	});
}

/**
 * 客服转接
 * @constructor
 */
export function serviceTransfer(data) {
	return request.post("service/transfer", data, {
		noAuth: true,
		kefu: true
	});
}

/**
 * 客服详细信息
 * @constructor
 */
export function serviceInfo(data) {
	return request.get("service/info", data, {
		noAuth: true,
		kefu: true
	});
}

/**
 * 客服反馈头部信息
 * @constructor
 */
export function serviceFeedBack() {
	return request.get("user/service/feedback");
}

/**
 * 客服反馈
 * @constructor
 */
export function feedBackPost(data) {
	return request.post("user/service/feedback", data);
}

/**
 * 检测登录code
 * @constructor
 */
export function codeStauts(data) {
	return request.get("user/code", data);
}
/**
 * 获取客服端口
 * @constructor
 */
export function getWorkermanUrl(data) {
	return request.get('get_workerman_url', {}, {
		noAuth: true
	})
}

/**
 * 客服扫码登录code
 * @constructor
 */
export function kefuScanLogin(data) {
	return request.post("user/code", data);
}
