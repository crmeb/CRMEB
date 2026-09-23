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
 * @description 交易数据 今天
 * @param {Object} param data {Object} 传值参数
 */
export function statisticTopTradeApi(params) {
  return request({
    url: '/statistic/trade/top_trade',
    method: 'get',
    params,
  });
}

/**
 * @description 交易概括
 * @param {Object} param data {Object} 传值参数
 */
export function statisticBottomTradeApi(params) {
  return request({
    url: '/statistic/trade/bottom_trade',
    method: 'get',
    params,
  });
}

/**
 * @description 订单统计数量
 * @param {Object} param data {Object} 传值参数
 */
export function getBasic(params) {
  return request({
    url: '/statistic/order/get_basic',
    method: 'get',
    params,
  });
}

/**
 * @description 订单统计折线图
 * @param {Object} param data {Object} 传值参数
 */
export function getTrend(params) {
  return request({
    url: '/statistic/order/get_trend',
    method: 'get',
    params,
  });
}
/**
 * @description 订单来源分析
 * @param {Object} param data {Object} 传值参数
 */
export function getChannel(params) {
  return request({
    url: '/statistic/order/get_channel',
    method: 'get',
    params,
  });
}
/**
 * @description 订单类型分析
 * @param {Object} param data {Object} 传值参数
 */
export function getType(params) {
  return request({
    url: '/statistic/order/get_type',
    method: 'get',
    params,
  });
}

/**
 * @description 账单记录列表
 * @param {Object} param data {Object} 传值参数
 */
export function getRecord(params) {
  return request({
    url: '/statistic/flow/get_record',
    method: 'get',
    params,
  });
}

/**
 * @description 余额统计数量
 * @param {Object} param data {Object} 传值参数
 */
export function getBalanceBasic(params) {
  return request({
    url: '/statistic/balance/get_basic',
    method: 'get',
    params,
  });
}

/**
 * @description 余额统计折线图
 * @param {Object} param data {Object} 传值参数
 */
export function getBalanceTrend(params) {
  return request({
    url: '/statistic/balance/get_trend',
    method: 'get',
    params,
  });
}
/**
 * @description 余额来源分析
 * @param {Object} param data {Object} 传值参数
 */
export function getBalanceChannel(params) {
  return request({
    url: '/statistic/balance/get_channel',
    method: 'get',
    params,
  });
}
/**
 * @description 余额类型分析
 * @param {Object} param data {Object} 传值参数
 */
export function getBalanceType(params) {
  return request({
    url: '/statistic/balance/get_type',
    method: 'get',
    params,
  });
}
/**
 * @description 渠道码统计
 * @param {Object} param data {Object} 传值参数
 */
export function wechatQrcodeStatistic(id, params) {
  return request({
    url: `app/wechat_qrcode/statistic/${id}`,
    method: 'get',
    params,
  });
}
