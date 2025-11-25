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
 * 统计数据
 */
export function getStatisticsInfo() {
	return request.get("admin/order/statistics", {}, {
		login: true
	});
}
/**
 * 订单月统计
 */
export function getStatisticsMonth(where) {
	return request.get("admin/order/data", where, {
		login: true
	});
}
/**
 * 订单月统计
 */
export function getAdminOrderList(where) {
	return request.get("admin/order/list", where, {
		login: true
	});
}
/**
 * 订单改价
 */
export function setAdminOrderPrice(data) {
	return request.post("admin/order/price", data, {
		login: true
	});
}
/**
 * 订单备注
 */
export function setAdminOrderRemark(data) {
	return request.post("admin/order/remark", data, {
		login: true
	});
}
/**
 * 订单详情
 */
export function getAdminOrderDetail(orderId) {
	return request.get("admin/order/detail/" + orderId, {}, {
		login: true
	});
}

/**
 * 退款订单详情
 */
export function getAdminRefundOrderDetail(orderId) {
	return request.get("admin/refund_order/detail/" + orderId, {}, {
		login: true
	});
}

/**
 * 订单发货信息获取
 */
export function getAdminOrderDelivery(orderId) {
	return request.get(
		"admin/order/delivery/gain/" + orderId, {}, {
			login: true
		}
	);
}

/**
 * 订单发货保存
 */
export function setAdminOrderDelivery(id, data) {
	return request.post("admin/order/delivery/keep/" + id, data, {
		login: true
	});
}
/**
 * 订单统计图
 */
export function getStatisticsTime(data) {
	return request.get("admin/order/time", data, {
		login: true
	});
}
/**
 * 线下付款订单确认付款
 */
export function setOfflinePay(data) {
	return request.post("admin/order/offline", data, {
		login: true
	});
}
/**
 * 订单确认退款
 */
export function setOrderRefund(data) {
	return request.post("admin/order/refund", data, {
		login: true
	});
}

/**
 * 获取快递公司
 * @returns {*}
 */
export function getLogistics(data) {
	return request.get("logistics", data, {
		login: false
	});
}

/**
 * 订单核销
 * @returns {*}
 */
export function orderVerific(verify_code, is_confirm) {
	return request.post("order/order_verific", {
		verify_code,
		is_confirm
	});
}

/**
 * 获取物流公司模板
 * @returns {*}
 */
export function orderExportTemp(data) {
	return request.get("admin/order/export_temp", data);
}

/**
 * 获取订单打印默认配置
 * @returns {*}
 */
export function orderDeliveryInfo() {
	return request.get("admin/order/delivery_info");
}

/**
 * 配送员列表
 * @returns {*}
 */
export function orderOrderDelivery() {
	return request.get("admin/order/delivery");
}

/**
 * 退款列表
 * @returns {*}
 */
export function orderRefund_order(where) {
	return request.get("admin/refund_order/list", where, {
		login: true
	});
}

/**
 * 订单备注（退款）
 */
export function setAdminRefundRemark(data) {
	return request.post("admin/refund_order/remark", data, {
		login: true
	});
}

/**
 * 订单同意退货
 */
export function agreeExpress(data) {
	return request.post("admin/order/agreeExpress", data, {
		login: true
	});
}