// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

import request from '@/libs/request';

/**
 * @description 商品统计 商品概括
 * @param {Object} param data {Object} 传值参数
 */
export function statisticBasicApi(params) {
  return request({
    url: '/statistic/product/get_basic',
    method: 'get',
    params,
  });
}

/**
 * @description 商品统计 商品概括 统计图
 * @param {Object} param data {Object} 传值参数
 */
export function statisticTrendApi(params) {
  return request({
    url: '/statistic/product/get_trend',
    method: 'get',
    params,
  });
}

/**
 * @description 商品统计 商品排行
 * @param {Object} param data {Object} 传值参数
 */
export function statisticProductListApi(params) {
  return request({
    url: '/statistic/product/get_product_ranking',
    method: 'get',
    params,
  });
}

/**
 * @description 商品统计 导出
 * @param {Object} param data {Object} 传值参数
 */
export function statisticProductExcel(params) {
  return request({
    url: '/statistic/product/get_excel',
    method: 'get',
    params,
  });
}

/**
 * @description 用户统计 用户概括
 * @param {Object} param data {Object} 传值参数
 */
export function statisticUserBasicApi(params) {
  return request({
    url: '/statistic/user/get_basic',
    method: 'get',
    params,
  });
}

/**
 * @description 用户统计 用户趋势
 * @param {Object} param data {Object} 传值参数
 */
export function statisticUserTrendApi(params) {
  return request({
    url: '/statistic/user/get_trend',
    method: 'get',
    params,
  });
}

/**
 * @description 用户统计 微信用户概括
 * @param {Object} param data {Object} 传值参数
 */
export function statisticWechatApi(params) {
  return request({
    url: '/statistic/user/get_wechat',
    method: 'get',
    params,
  });
}

/**
 * @description 用户统计 微信用户趋势
 * @param {Object} param data {Object} 传值参数
 */
export function statisticWechatTrendApi(params) {
  return request({
    url: '/statistic/user/get_wechat_trend',
    method: 'get',
    params,
  });
}

/**
 * @description 用户统计 微信用户地域
 * @param {Object} param data {Object} 传值参数
 */
export function statisticWechatRegionApi(params) {
  return request({
    url: '/statistic/user/get_region',
    method: 'get',
    params,
  });
}

/**
 * @description 用户统计 用户性别
 * @param {Object} param data {Object} 传值参数
 */
export function statisticWechatSexApi(params) {
  return request({
    url: '/statistic/user/get_sex',
    method: 'get',
    params,
  });
}

/**
 * @description 用户统计 导出
 * @param {Object} param data {Object} 传值参数
 */
export function statisticUserExcel(params) {
  return request({
    url: '/statistic/user/get_excel',
    method: 'get',
    params,
  });
}

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
