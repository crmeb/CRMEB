// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

import request from '@/libs/request';

/**
 * 实物商品订单 - 列表
 */
export function getOrderList(data) {
  return request.get('normal/order/list', { params: data });
}

/**
 * 实物商品订单 - 统计
 * @param {Object} params 搜索参数 {keyword, date_range, pay_type}
 */
export function getOrderStatistics(params = {}) {
  return request.get('normal/order/statistics', { params });
}

/**
 * 实物商品订单 - 详情
 */
export function getOrderDetail(orderNo) {
  return request.get('normal/order/' + orderNo);
}

/**
 * 实物商品订单 - 发货
 */
export function shipment(data) {
  return request.post('normal/order/shipment', data);
}

/**
 * 实物商品订单 - 批量发货
 */
export function batchShipment(data) {
  return request.post('normal/order/batch_shipment', data);
}

/**
 * 实物商品订单 - 获取发货详情（用户信息 + 商品列表）
 */
export function getShipmentDetail(orderNo) {
  return request.get('normal/order/shipment/detail/' + orderNo);
}

/**
 * 实物商品订单 - 获取配送员列表
 */
export function getDeliveryPersons() {
  return request.get('normal/order/shipment/delivery_persons');
}

/**
 * 实物商品订单 - 获取快递公司列表
 */
export function getExpressList() {
  return request.get('normal/order/express_list');
}

/**
 * 实物商品订单 - 获取发件人配置信息
 */
export function getSheetInfo() {
  return request.get('normal/order/shipment/sheet_info');
}

/**
 * 实物商品订单 - 获取电子面单模板
 */
export function getExpressTemp(params) {
  return request.get('normal/order/shipment/express_temp', { params });
}

/**
 * 实物商品订单 - 获取可拆单商品列表
 */
export function getSplitCartInfo(orderNo) {
  return request.get('normal/order/shipment/split_cart_info/' + orderNo);
}

/**
 * 实物商品订单 - 确认收货
 */
export function confirmReceipt(data) {
  return request.post('normal/order/confirm_receipt', data);
}

/**
 * 实物商品订单 - 修改备注
 */
export function updateRemark(data) {
  return request.put('normal/order/remark', data);
}

/**
 * 实物商品订单 - 修改收货地址
 */
export function editAddress(data) {
  return request.put('normal/order/edit_address', data);
}

/**
 * 实物商品订单 - 删除订单
 */
export function deleteOrder(id) {
  return request.delete('normal/order/delete/' + id);
}

/**
 * 实物商品订单 - 获取退款表单
 */
export function getRefundForm(orderNo) {
  return request.get('normal/order/get_refund_form/' + orderNo);
}

/**
 * 实物商品订单 - 执行退款
 */
export function processRefund(data) {
  return request.post('normal/order/process_refund', data);
}

/**
 * 实物商品订单 - 小票打印
 */
export function getPrintReceipt(orderNo) {
  return request.get('normal/order/print_receipt/' + orderNo);
}

/**
 * 实物商品订单 - 配货单打印
 */
export function getPrintPickList(orderNo) {
  return request.get('normal/order/print_picklist/' + orderNo);
}

/**
 * 实物商品订单 - 获取操作记录
 */
export function getOrderRecord(params) {
  return request.get('normal/order/record/list', { params });
}

/**
 * 实物商品订单 - 导出
 */
export function exportOrderList(data) {
  return request.get('normal/order/export', { params: data });
}

/**
 * 实物商品订单 - 订单编辑表单数据
 */
export function getEditOrderForm(orderNo) {
  return request.get('normal/order/edit_price/' + orderNo);
}

/**
 * 实物商品订单 - 保存编辑
 */
export function saveEditOrder(data) {
  return request.put('normal/order/update_price', data);
}

/**
 * 实物商品订单 - 保存积分（赠送积分 / 退回积分）
 */
export function saveOrderIntegral(data) {
  return request.put('normal/order/update_integral', data);
}

/**
 * 实物商品订单 - 物流查询
 */
export function getOrderExpress(orderNo) {
  return request.get('normal/order/express/' + orderNo);
}
