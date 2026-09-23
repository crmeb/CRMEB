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
 * @description 电子面单驱动 列表
 */
export function expressDriverListApi(params) {
  return request({
    url: 'driver/express_driver/list',
    method: 'get',
    params,
  });
}

/**
 * @description 电子面单驱动 同步驱动信息
 */
export function expressDriverSyncApi() {
  return request({
    url: 'driver/express_driver/sync',
    method: 'post',
  });
}

/**
 * @description 电子面单驱动 修改状态
 */
export function expressDriverSetStatusApi(id, status) {
  return request({
    url: `driver/express_driver/set_status/${id}`,
    method: 'put',
    data: { status },
  });
}

/**
 * @description 电子面单驱动 详情
 */
export function expressDriverDetailApi(id) {
  return request({
    url: `driver/express_driver/detail/${id}`,
    method: 'get',
  });
}

/**
 * @description 电子面单驱动 配置字段
 */
export function expressDriverConfigFieldsApi(driverKey) {
  return request({
    url: `driver/express_driver/config_fields/${driverKey}`,
    method: 'get',
  });
}

/**
 * @description 电子面单驱动 获取所有已扫描的驱动
 */
export function expressDriverDriversApi() {
  return request({
    url: 'driver/express_driver/drivers',
    method: 'get',
  });
}

/**
 * @description 电子面单驱动 获取启用的驱动列表
 */
export function expressDriverEnabledApi() {
  return request({
    url: 'driver/express_driver/enabled',
    method: 'get',
  });
}

/**
 * @description 电子面单驱动 保存驱动配置
 */
export function saveExpressDriverConfigApi(id, data) {
  return request({
    url: `driver/express_driver/save_config/${id}`,
    method: 'post',
    data,
  });
}

/**
 * @description 电子面单驱动 批量排序
 */
export function expressDriverSortApi(data) {
  return request({
    url: 'driver/express_driver/sort',
    method: 'put',
    data,
  });
}
