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
 * @description 物流查询驱动 列表
 * @param {Object} param params {Object} 传值参数
 */
export function logisticsDriverListApi(params) {
  return request({
    url: 'driver/logistics_driver/list',
    method: 'get',
    params,
  });
}

/**
 * @description 物流查询驱动 同步驱动信息
 */
export function logisticsDriverSyncApi() {
  return request({
    url: 'driver/logistics_driver/sync',
    method: 'post',
  });
}

/**
 * @description 物流查询驱动 修改状态
 * @param {Number} param id {Number} 驱动ID
 * @param {Number} param status {Number} 状态
 */
export function logisticsDriverSetStatusApi(id, status) {
  return request({
    url: `driver/logistics_driver/set_status/${id}`,
    method: 'put',
    data: { status },
  });
}

/**
 * @description 物流查询驱动 详情
 * @param {Number} param id {Number} 驱动ID
 */
export function logisticsDriverDetailApi(id) {
  return request({
    url: `driver/logistics_driver/detail/${id}`,
    method: 'get',
  });
}

/**
 * @description 物流查询驱动 配置字段
 * @param {String} param driverKey {String} 驱动标识
 */
export function logisticsDriverConfigFieldsApi(driverKey) {
  return request({
    url: `driver/logistics_driver/config_fields/${driverKey}`,
    method: 'get',
  });
}

/**
 * @description 物流查询驱动 启用的驱动列表
 */
export function logisticsDriverEnabledApi() {
  return request({
    url: 'driver/logistics_driver/enabled',
    method: 'get',
  });
}

/**
 * @description 保存驱动配置
 * @param {Number} id 驱动ID
 * @param {Object} data 配置数据
 */
export function saveLogisticsDriverConfigApi(id, data) {
  return request({
    url: `driver/logistics_driver/save_config/${id}`,
    method: 'post',
    data,
  });
}
