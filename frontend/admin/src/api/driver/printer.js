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
 * @description 打印机渠道 - 列表
 */
export function printerChannelListApi(params) {
  return request({
    url: 'driver/printer_channel/list',
    method: 'get',
    params,
  });
}

/**
 * @description 打印机渠道 - 详情
 * @param {Number} id
 */
export function printerChannelDetailApi(id) {
  return request({
    url: `driver/printer_channel/${id}`,
    method: 'get',
  });
}

/**
 * @description 打印机渠道 - 新增
 * @param {Object} data
 */
export function printerChannelSaveApi(data) {
  return request({
    url: 'driver/printer_channel/save',
    method: 'post',
    data,
  });
}

/**
 * @description 打印机渠道 - 更新
 * @param {Number} id
 * @param {Object} data
 */
export function printerChannelUpdateApi(id, data) {
  return request({
    url: `driver/printer_channel/${id}`,
    method: 'put',
    data,
  });
}

/**
 * @description 打印机渠道 - 修改状态
 * @param {Number} id
 * @param {Number} status 0=关闭 1=开启
 */
export function printerChannelSetStatusApi(id, status) {
  return request({
    url: `driver/printer_channel/set_status/${id}/${status}`,
    method: 'put',
  });
}

/**
 * @description 打印机渠道 - 删除
 * @param {Number} id
 */
export function printerChannelDeleteApi(id) {
  return request({
    url: `driver/printer_channel/del/${id}`,
    method: 'delete',
  });
}

/**
 * @description 打印机渠道 - 获取驱动列表
 */
export function printerChannelDriversApi() {
  return request({
    url: 'driver/printer_channel/drivers',
    method: 'get',
  });
}

/**
 * @description 打印机渠道 - 批量排序
 * @param {Object} data {sort_data: [{id, sort}]}
 */
export function printerChannelSortApi(data) {
  return request({
    url: 'driver/printer_channel/sort',
    method: 'put',
    data,
  });
}

/**
 * @description 打印机渠道 - 获取小票打印内容配置
 * @param {Number} id
 */
export function printerChannelContentApi(id) {
  return request({
    url: `driver/printer_channel/content/${id}`,
    method: 'get',
  });
}

/**
 * @description 打印机渠道 - 保存小票打印内容配置
 * @param {Number} id
 * @param {Object} data
 */
export function printerChannelSaveContentApi(id, data) {
  return request({
    url: `driver/printer_channel/save_content/${id}`,
    method: 'post',
    data,
  });
}
