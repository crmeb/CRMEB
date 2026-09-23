/**
 * 秒杀模块 API
 */
import request from '@/libs/request';

/**
 * 秒杀商品列表
 */
export function seckillListApi(params) {
  return request({
    url: 'marketing/seckill',
    method: 'GET',
    params,
  });
}

/**
 * 秒杀商品详情
 */
export function seckillInfoApi(id) {
  return request({
    url: `marketing/seckill/${id}`,
    method: 'GET',
  });
}

/**
 * 保存秒杀商品（新增/编辑）
 */
export function seckillAddApi(data) {
  return request({
    url: `marketing/seckill/${data.id || 0}`,
    method: 'POST',
    data,
  });
}

/**
 * 修改秒杀商品状态
 */
export function seckillStatusApi(data) {
  return request({
    url: `marketing/seckill/set_status/${data.id}/${data.status}`,
    method: 'PUT',
  });
}

/**
 * 删除秒杀商品
 */
export function seckillDelApi(id) {
  return request({
    url: `marketing/seckill/${id}`,
    method: 'DELETE',
  });
}

/**
 * 秒杀活动列表
 */
export function seckillActivityListApi(params) {
  return request({
    url: 'marketing/seckill_activity/list',
    method: 'GET',
    params,
  });
}

/**
 * 秒杀活动详情
 */
export function seckillActivityInfoApi(id) {
  return request({
    url: `marketing/seckill_activity/info/${id}`,
    method: 'GET',
  });
}

/**
 * 保存秒杀活动（新增/编辑）
 */
export function seckillActivityAddApi(data) {
  return request({
    url: `marketing/seckill_activity/save/${data.id || 0}`,
    method: 'POST',
    data,
  });
}

/**
 * 删除秒杀活动
 */
export function seckillActivityDelApi(id) {
  return request({
    url: `marketing/seckill_activity/del/${id}`,
    method: 'DELETE',
  });
}

/**
 * 修改秒杀活动状态
 */
export function seckillActivityStatusApi(data) {
  return request({
    url: `marketing/seckill_activity/status/${data.id}/${data.status}`,
    method: 'PUT',
  });
}

/**
 * 秒杀时间段列表
 */
export function seckillTimeListApi() {
  return request({
    url: 'marketing/seckill/time_list',
    method: 'GET',
  });
}

/**
 * 秒杀统计-头部卡片
 */
export function getseckillStatistics(id) {
  return request({
    url: `marketing/seckill/statistics/head/${id}`,
    method: 'GET',
  });
}

/**
 * 秒杀统计-参与人员
 */
export function getseckillStatisticsPeople(id, params) {
  return request({
    url: `marketing/seckill/statistics/people/${id}`,
    method: 'GET',
    params,
  });
}

/**
 * 秒杀统计-订单列表
 */
export function getseckillStatisticsOrder(id, params) {
  return request({
    url: `marketing/seckill/statistics/order/${id}`,
    method: 'GET',
    params,
  });
}

/**
 * 获取商品规格
 */
export function productAttrsApi(id, type) {
  return request({
    url: `product/product/attrs/${id}/${type}`,
    method: 'GET',
  });
}

/**
 * 导出秒杀商品列表
 */
export function exportSeckillListApi(params) {
  return request({
    url: 'export/seckill_list',
    method: 'GET',
    params,
  });
}

/**
 * 秒杀配置列表
 */
export function seckillConfigListApi(params) {
  return request({
    url: 'marketing/seckill_config',
    method: 'GET',
    params,
  });
}

/**
 * 保存秒杀配置（新增/编辑）
 */
export function seckillConfigSaveApi(data) {
  return request({
    url: 'marketing/seckill_config',
    method: 'POST',
    data,
  });
}

/**
 * 获取秒杀配置详情
 */
export function seckillConfigInfoApi(id) {
  return request({
    url: `marketing/seckill_config/${id}`,
    method: 'GET',
  });
}

/**
 * 删除秒杀配置
 */
export function seckillConfigDelApi(id) {
  return request({
    url: `marketing/seckill_config/${id}`,
    method: 'DELETE',
  });
}

/**
 * 修改秒杀配置状态
 */
export function seckillConfigStatusApi(data) {
  return request({
    url: `marketing/seckill_config/set_status/${data.id}/${data.status}`,
    method: 'PUT',
  });
}
