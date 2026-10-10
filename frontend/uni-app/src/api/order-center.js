/**
 * OrderCenter 中台 API
 * 调用后端 OrderCenter 接口获取订单数据
 */
import request from '@/utils/request.js';

const BASE = '';

/**
 * 获取订单列表
 * @param {Object} params { page, limit, status }
 */
export function getCenterOrderList(params) {
  return request.get('center/order/list', params);
}

/**
 * 获取订单统计
 * @param {Object} params 查询参数
 */
export function getCenterOrderStat(params) {
  return request.get('center/order/stat', params);
}

/**
 * 获取订单详情
 * @param {string} orderNo 订单号
 */
export function getCenterOrderDetail(orderNo) {
  return request.get(`center/order/detail/${orderNo}`);
}

/**
 * 订单确认（加载商品信息、积分、优惠券数据）
 * @param {Object} params { product_id, sku_unique, product_type, num }
 */
export function getCenterOrderConfirm(params) {
  // 携带时间戳避免浏览器缓存旧的确认数据（缓存会导致优惠券模块显隐/券列表陈旧）
  return request.get('center/order/confirm', { ...params, _t: Date.now() });
}

/**
 * 计算订单金额（切换优惠券/积分时重算）
 * @param {Object} params { key, coupon_id, use_integral, address_id }
 */
export function postCenterOrderComputed(params) {
  return request.post('center/order/computed', params);
}

/**
 * 统一下单接口
 * @param {Object} params { product_id, order_data, product_type, sku_unique, num }
 * @param {string} submitApi 自定义提交API路径（默认 center/order/create）
 */
export function submitOrder(params, submitApi = '') {
  const apiPath = submitApi || 'center/order/create';
  return request.post(apiPath, params, {
    headers: { 'X-Product-Type': params.product_type || 'normal' },
  });
}

/**
 * 获取退款理由列表
 */
export function getRefundReason() {
  return request.get('order/refund/reason');
}

/**
 * 获取退款商品列表
 * @param {string} orderId 订单ID
 */
export function getRefundCartInfo(orderId) {
  return request.get(`order/refund/cart_info/${orderId}`);
}

/**
 * 提交退款申请商品列表
 * @param {Object} data { id, cart_ids }
 */
export function postRefundCartInfo(data) {
  return request.post('order/refund/cart_info', data);
}

/**
 * 提交退款申请
 * @param {number} id 订单ID
 * @param {Object} data 退款数据
 */
export function submitRefund(id, data) {
  return request.post(`order/refund/apply/${id}`, data);
}

/**
 * 提交退款物流信息
 * @param {Object} data 物流数据
 */
export function submitRefundExpress(data) {
  return request.post('order/refund/express', data);
}

/**
 * 获取退款订单详情
 * @param {string} uni 订单号
 */
export function getRefundDetail(uni) {
  return request.get(`order/refund/detail/${uni}`);
}

/**
 * 获取退款表单配置
 * @param {string} orderNo 订单号
 */
export function getRefundFormConfig(orderNo) {
  return request.get(`order/refund/form_config/${orderNo}`);
}

/**
 * 取消订单
 * @param {string} orderNo 订单号
 */
export function cancelOrder(orderNo) {
  return request.post('order/cancel', { id: orderNo });
}

/**
 * 确认收货
 * @param {string} orderNo 订单号
 */
export function takeOrder(orderNo) {
  return request.post('order/take', { uni: orderNo });
}

/**
 * 删除订单
 * @param {string} orderNo 订单号
 */
export function delOrder(orderNo) {
  return request.post('order/del', { uni: orderNo });
}

/**
 * 获取原始订单详情（从 order_center 中台表，通过 center 接口）
 * 返回数据含 source_id 字段即 store_order.id
 * @param {string} orderNo 订单号
 */
export function getStoreOrderDetail(orderNo) {
  return request.get(`center/order/detail/${orderNo}`);
}

/**
 * 获取订单可用售后类型
 * 后端经三维规则（订单大类 × 产品类型 × 活动类型）合并后返回
 * @param {string} orderNo 订单号
 */
export function getAfterSaleAvailableTypes(orderNo) {
  return request.get(`after_sale/available_types/${orderNo}`);
}

/**
 * 提交售后申请
 * @param {Object} data { order_no, type, refund_price, reason, evidence_images, product_snapshot }
 */
export function submitAfterSale(data) {
  return request.post('after_sale/apply', data);
}

/**
 * 取消售后申请
 * @param {string} afterSaleNo 售后单号
 */
export function cancelAfterSale(afterSaleNo) {
  return request.post('after_sale/cancel', { after_sale_no: afterSaleNo });
}

/**
 * 填写退货物流
 * @param {Object} data { after_sale_no, delivery_type, delivery_no, images }
 */
export function deliverReturnAfterSale(data) {
  return request.post('after_sale/deliver_return', data);
}

/**
 * 获取用户售后列表
 * @param {Object} params { page, limit, status_group }
 *   status_group 状态分组：空=全部，processing=处理中，completed=已完成，canceled=已取消
 */
export function getAfterSaleList(params) {
  return request.get('after_sale/list', params);
}

/**
 * 获取售后申请表单配置（数据驱动渲染）
 * 后端按产品类型 Handler 返回动态字段，前端据此渲染申请表单
 * @param {string} orderNo 订单号
 * @param {string} type 售后类型 refund_only/return_refund/exchange/resend/repair
 */
export function getAfterSaleFormConfig(orderNo, type) {
  return request.get(`after_sale/form_config/${orderNo}/${type}`);
}

/**
 * 获取售后详情（含信息+时间线+可操作按钮）
 * @param {string} afterSaleNo 售后单号
 */
export function getAfterSaleDetail(afterSaleNo) {
  return request.get(`after_sale/detail/${afterSaleNo}`);
}

/**
 * 用户确认收货（补发/维修完成后）
 * @param {string} afterSaleNo 售后单号
 */
export function confirmAfterSaleReceive(afterSaleNo) {
  return request.post('after_sale/confirm_receive', { after_sale_no: afterSaleNo });
}
