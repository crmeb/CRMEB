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
 * @description 存储驱动 列表
 * @param {Object} param params {Object} 传值参数
 */
export function uploadDriverListApi(params) {
  return request({
    url: 'driver/upload_driver/list',
    method: 'get',
    params,
  });
}

/**
 * @description 存储驱动 同步驱动信息
 */
export function uploadDriverSyncApi() {
  return request({
    url: 'driver/upload_driver/sync',
    method: 'post',
  });
}

/**
 * @description 存储驱动 修改状态
 * @param {Number} param id {Number} 驱动ID
 * @param {Number} param status {Number} 状态
 */
export function uploadDriverSetStatusApi(id, status) {
  return request({
    url: `driver/upload_driver/set_status/${id}`,
    method: 'put',
    data: { status },
  });
}

/**
 * @description 存储驱动 详情
 * @param {Number} param id {Number} 驱动ID
 */
export function uploadDriverDetailApi(id) {
  return request({
    url: `driver/upload_driver/detail/${id}`,
    method: 'get',
  });
}

/**
 * @description 存储驱动 配置字段
 * @param {String} param driver_key {String} 驱动标识
 */
export function uploadDriverConfigFieldsApi(driverKey) {
  return request({
    url: `driver/upload_driver/config_fields/${driverKey}`,
    method: 'get',
  });
}

/**
 * @description 存储驱动 存储空间配置字段
 * @param {String} param driver_key {String} 驱动标识
 */
export function uploadDriverBucketFieldsApi(driverKey) {
  return request({
    url: `driver/upload_driver/bucket_fields/${driverKey}`,
    method: 'get',
  });
}

/**
 * @description 存储驱动 能力声明
 * @param {String} param driver_key {String} 驱动标识
 */
export function uploadDriverCapabilitiesApi(driverKey) {
  return request({
    url: `driver/upload_driver/capabilities/${driverKey}`,
    method: 'get',
  });
}

/**
 * @description 存储驱动 启用的驱动列表
 */
export function uploadDriverEnabledApi() {
  return request({
    url: 'driver/upload_driver/enabled',
    method: 'get',
  });
}

/**
 * @description 获取上传公共配置（缩略图和水印）
 * @param {String} driverKey 驱动标识
 */
export function getUploadCommonConfigApi(driverKey) {
  return request({
    url: `driver/upload_driver/common_config/${driverKey}`,
    method: 'get',
  });
}

/**
 * @description 保存上传公共配置（缩略图和水印）
 * @param {String} driverKey 驱动标识
 * @param {Object} data 配置数据
 */
export function saveUploadCommonConfigApi(driverKey, data) {
  return request({
    url: `driver/upload_driver/save_common_config/${driverKey}`,
    method: 'post',
    data,
  });
}

/**
 * @description 保存驱动配置
 * @param {Number} id 驱动ID
 * @param {Object} data 基础配置、驱动字段及可选 thumb_watermark_config，同一事务保存
 */
export function saveDriverConfigApi(id, data) {
  return request({
    url: `driver/upload_driver/save_config/${id}`,
    method: 'post',
    data,
  });
}
