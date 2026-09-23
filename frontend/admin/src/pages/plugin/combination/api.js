/**
 * 拼团模块 API
 */
import request from '@/libs/request';

/**
 * 拼团商品列表
 */
export function combinationListApi(params) {
  return request({
    url: 'marketing/combination',
    method: 'GET',
    params,
  });
}

/**
 * 修改拼团商品状态
 */
export function combinationSetStatusApi(data) {
  return request({
    url: `marketing/combination/set_status/${data.id}/${data.status}`,
    method: 'PUT',
  });
}

/**
 * 拼团统计（全局）
 */
export function statisticsApi() {
  return request({
    url: 'marketing/combination/statistics',
    method: 'GET',
  });
}

/**
 * 拼团商品详情
 */
export function combinationInfoApi(id) {
  return request({
    url: `marketing/combination/${id}`,
    method: 'GET',
  });
}

/**
 * 保存拼团商品（新增/编辑）
 */
export function combinationCreatApi(data) {
  return request({
    url: `marketing/combination/${data.id || 0}`,
    method: 'POST',
    data,
  });
}

/**
 * 拼团列表（已开团的）
 */
export function combineListApi(params) {
  return request({
    url: 'marketing/combination/combine/list',
    method: 'GET',
    params,
  });
}

/**
 * 拼团人列表
 */
export function orderPinkListApi(id) {
  return request({
    url: `marketing/combination/order_pink/${id}`,
    method: 'GET',
  });
}

/**
 * 拼团导出
 */
export function storeCombinationApi(data) {
  return request({
    url: 'export/combination_list',
    method: 'GET',
    params: data,
  });
}

/**
 * 拼团统计-头部卡片
 */
export function getcombinationStatistics(id) {
  return request({
    url: `marketing/combination/statistics/head/${id}`,
    method: 'GET',
  });
}

/**
 * 拼团统计-参与人员
 */
export function getcombinationStatisticsPeople(id, params) {
  return request({
    url: `marketing/combination/statistics/list/${id}`,
    method: 'GET',
    params,
  });
}

/**
 * 拼团统计-订单列表
 */
export function getcombinationStatisticsOrder(id, params) {
  return request({
    url: `marketing/combination/statistics/order/${id}`,
    method: 'GET',
    params,
  });
}

/**
 * 拼团立即成团
 */
export function combineJoinApi(id) {
  return request({
    url: `marketing/combination/immediately/${id}`,
    method: 'GET',
  });
}
