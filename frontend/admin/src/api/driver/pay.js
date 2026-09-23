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
 * @description 支付方式 - 列表
 * @param {Object} param params {Object} 传值参数
 */
export function payChannelListApi(params) {
  return request({
    url: 'driver/pay_channel/list',
    method: 'get',
    params,
  });
}

/**
 * @description 支付方式 - 详情
 * @param {Number} param id {Number} 支付方式id
 */
export function payChannelDetailApi(id) {
  return request({
    url: `driver/pay_channel/${id}`,
    method: 'get',
  });
}

/**
 * @description 支付方式 - 更新配置
 * @param {Number} param id {Number} 支付方式id
 * @param {Object} param data {Object} 传值参数
 */
export function payChannelUpdateApi(id, data) {
  return request({
    url: `driver/pay_channel/${id}`,
    method: 'put',
    data,
  });
}

/**
 * @description 支付方式 - 修改状态
 * @param {Number} param id {Number} 支付方式id
 * @param {Number} param status {Number} 状态：0=关闭，1=开启
 */
export function payChannelSetStatusApi(id, status) {
  return request({
    url: `driver/pay_channel/set_status/${id}/${status}`,
    method: 'put',
  });
}

/**
 * @description 支付方式 - 获取驱动列表
 */
export function payChannelDriversApi() {
  return request({
    url: 'driver/pay_channel/drivers',
    method: 'get',
  });
}

/**
 * @description 支付方式 - 批量排序
 * @param {Object} param data {Object} 传值参数 {sort_data: [{id, sort}]}
 */
export function payChannelSortApi(data) {
  return request({
    url: 'driver/pay_channel/sort',
    method: 'put',
    data,
  });
}
