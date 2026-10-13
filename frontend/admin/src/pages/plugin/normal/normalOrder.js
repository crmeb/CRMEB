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

/**
 * 订单业务状态：与订单列表 OrderTable 的 getBusinessStatus 同口径，
 * 供详情弹窗等场景复用（列表/详情状态展示保持一致）
 */
export function getOrderBusinessStatus(row = {}) {
  if (row.is_del === 1) {
    return { code: 'deleted', text: '已删除', type: 'danger' };
  }
  if (row.status_code === 'pending_payment') {
    return { code: 'pending_payment', text: '待支付', type: 'warning' };
  }
  if (row.status_code === 'canceled') {
    return { code: 'canceled', text: '已取消', type: 'danger' };
  }
  if (row.status_code === 'after_sale' || row.status_code === 'refunding') {
    return { code: 'after_sale', text: '退换/售后', type: 'info' };
  }
  if (row.status_code === 'refunded') {
    return { code: 'refunded', text: '已退款', type: 'info' };
  }
  if (row.status_code === 'processing') {
    // 到店自提订单显示待到店；其余按发货进度拆分待发货/待收货
    if (row.extra_info?.delivery_type === 'pickup') {
      return { code: 'pending_pickup', text: '待到店', type: 'warning' };
    }
    if (!row.delivery_time || row.delivery_time === 0) {
      return { code: 'pending_shipped', text: '待发货', type: 'danger' };
    }
    return { code: 'pending_receive', text: '待收货', type: 'primary' };
  }
  if (row.status_code === 'pending_review') {
    return { code: 'pending_review', text: '待评价', type: 'success' };
  }
  if (row.status_code === 'completed') {
    return { code: 'completed', text: '已完成', type: 'success' };
  }
  return { code: row.status_code, text: row.status_code || '未知', type: 'info' };
}
