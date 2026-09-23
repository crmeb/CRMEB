// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

import request from '@/libs/request';

/**
 * @description 发票头部统计
 * @param {Object} param data {Object} 传值参数
 */
export function orderInvoiceChart(data) {
  return request({
    url: 'order/invoice/chart',
    method: 'get',
    params: data,
  });
}

/**
 * @description 发票头部统计
 * @param {Object} param data {Object} 传值参数
 */
export function orderInvoiceList(data) {
  return request({
    url: 'order/invoice/list',
    method: 'get',
    params: data,
  });
}

/**
 * @description 发票提交订单
 * @param {Object} param data {Object} 传值参数
 */
export function orderInvoiceSet(id, data) {
  return request({
    url: `order/invoice/set/${id}`,
    method: 'post',
    data,
  });
}

/**
 * @description 发票订单详情；
 * @param {Object} param data {Object} 传值参数
 */
export function orderInvoiceInfo(id) {
  return request({
    url: `order/invoice_order_info/${id}`,
    method: 'get',
  });
}

/**
 * @description 订单数据--列表
 * @param {Object} param data {Object} 传值参数
 */
export function getOrdes(data) {
  return request({
    url: '/order/chart',
    method: 'get',
    params: data,
  });
}

/**
 * @description 获取快递公司列表
 */
export function getExpressData() {
  return request({
    url: 'normal/order/express_list',
    method: 'get',
  });
}

/**
 * @description 订单物流信息
 * @param {String} orderNo 订单号
 */
export function getExpress(orderNo) {
  return request({
    url: `normal/order/express/${orderNo}`,
    method: 'get',
  });
}

/**
 * 收银订单
 */
export function orderScanList(data) {
  return request({
    url: 'user/scan_list',
    method: 'get',
    params: data,
  });
}

/**
 * 线下收款码
 */
export function orderOfflineScan(id) {
  return request({
    url: 'user/offline_scan',
    method: 'get',
    params: id,
  });
}

/**
 * @description 发票管理
 * @param id  发票id
 */
export function invoiceIssuanceUrl(id) {
  return request({
    url: `/order/invoice_issuance_url/${id}`,
    method: 'get',
  });
}
/**
 * @description 发票下载
 * @param id  发票id
 */
export function downInvoice(id) {
  return request({
    url: `/order/down_invoice/${id}`,
    method: 'get',
  });
}
/**
 * @description 开具负数发票
 * @param id  发票id
 */
export function redInvoiceIssuance(id) {
  return request({
    url: `/order/red_invoice_issuance/${id}`,
    method: 'get',
  });
}
/**
 * @description 发票状态修改
 * @param id  发票id
 * @param data  发票信息
 */
export function saveInvoiceInfo(id, data) {
  return request({
    url: `/order/save_invoice_info/${id}`,
    method: 'post',
    data: data,
  });
}
/**
 * @description 发票分类搜索
 * @param name  发票分类名称
 */
export function invoiceCategory(name) {
  return request({
    url: `/order/invoice_category`,
    method: 'get',
    params: name,
  });
}
/**
 * @description 获取电子发票配置
 */
export function invoiceConfig() {
  return request({
    url: `/order/elec_invoice_config`,
    method: 'get',
  });
}
