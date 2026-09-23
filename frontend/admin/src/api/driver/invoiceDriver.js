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
 * @description 发票驱动 列表
 * @param {Object} param params {Object} 传值参数
 */
export function invoiceDriverListApi(params) {
  return request({
    url: 'driver/invoice_driver/list',
    method: 'get',
    params,
  });
}

/**
 * @description 发票驱动 同步驱动信息
 */
export function invoiceDriverSyncApi() {
  return request({
    url: 'driver/invoice_driver/sync',
    method: 'post',
  });
}

/**
 * @description 发票驱动 修改状态
 * @param {Number} id 驱动ID
 * @param {Number} status 状态
 */
export function invoiceDriverSetStatusApi(id, status) {
  return request({
    url: `driver/invoice_driver/set_status/${id}`,
    method: 'put',
    data: { status },
  });
}

/**
 * @description 发票驱动 详情
 * @param {Number} id 驱动ID
 */
export function invoiceDriverDetailApi(id) {
  return request({
    url: `driver/invoice_driver/detail/${id}`,
    method: 'get',
  });
}

/**
 * @description 发票驱动 配置字段
 * @param {String} driverKey 驱动标识
 */
export function invoiceDriverConfigFieldsApi(driverKey) {
  return request({
    url: `driver/invoice_driver/config_fields/${driverKey}`,
    method: 'get',
  });
}

/**
 * @description 发票驱动 获取所有已扫描的驱动
 */
export function invoiceDriverDriversApi() {
  return request({
    url: 'driver/invoice_driver/drivers',
    method: 'get',
  });
}

/**
 * @description 发票驱动 获取启用的驱动列表
 */
export function invoiceDriverEnabledApi() {
  return request({
    url: 'driver/invoice_driver/enabled',
    method: 'get',
  });
}

/**
 * @description 发票驱动 保存驱动配置
 * @param {Number} id 驱动ID
 * @param {Object} data 配置数据
 */
export function saveInvoiceDriverConfigApi(id, data) {
  return request({
    url: `driver/invoice_driver/save_config/${id}`,
    method: 'post',
    data,
  });
}

/**
 * @description 发票驱动 批量排序
 * @param {Object} data 排序数据
 */
export function invoiceDriverSortApi(data) {
  return request({
    url: 'driver/invoice_driver/sort',
    method: 'put',
    data,
  });
}
