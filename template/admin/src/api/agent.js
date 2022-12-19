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
 * @description 分销 -- 列表
 * @param {Object} param params {Object} 传值参数
 */
export function agentListApi(params) {
  return request({
    url: 'agent/index',
    method: 'get',
    params,
  });
}

/**
 * @description 修改上级用户
 * @param {Object} param params {Object} 传值参数
 */
export function agentSpreadApi(data) {
  return request({
    url: 'agent/spread',
    method: 'PUT',
    data,
  });
}

/**
 * @description 分销 -- 表头
 * @param {Object} param params {Object} 传值参数
 */
export function statisticsApi(params) {
  return request({
    url: 'agent/statistics',
    method: 'get',
    params,
  });
}

/**
 * @description 分销 -- 推广人,订单列表
 * @param {Object} param params {Object} 传值参数
 * @param {String} param url {String} 请求地址
 */
export function stairListApi(url, params) {
  return request({
    url: url,
    method: 'get',
    params,
  });
}

/**
 * @description 分销 -- 公众号推广二维码
 * @param {Object} param params {Object} 传值参数
 */
export function lookCodeApi(params) {
  return request({
    url: 'agent/look_code',
    method: 'get',
    params,
  });
}

/**
 * @description 分销 -- 小程序推广二维码
 * @param {Object} param params {Object} 传值参数
 */
export function lookxcxCodeApi(params) {
  return request({
    url: 'agent/look_xcx_code',
    method: 'get',
    params,
  });
}

/**
 * @description 分销 -- h5推广二维码
 * @param {Object} param params {Object} 传值参数
 */
export function lookh5CodeApi(params) {
  return request({
    url: 'agent/look_h5_code',
    method: 'get',
    params,
  });
}

/**
 * @description 分销 -- 用户推广列表导出
 */
export function userAgentApi(data) {
  return request({
    url: `export/userAgent`,
    method: 'get',
    params: data,
  });
}

/**
 * @description 事业部--列表
 * @param {Object} param data {Object} 传值参数
 */
export function regionList(data) {
  return request({
    url: 'agent/division/list',
    method: 'get',
    params: data,
  });
}

/**
 * @description 代理商申请--列表
 * @param {Object} param data {Object} 传值参数
 */
export function divisionList(data) {
  return request({
    url: 'agent/division/agent_apply/list',
    method: 'get',
    params: data,
  });
}

/**
 * @description 代理商添加--表单
 * @param {Object} param data {Object} 传值参数
 */
export function agentFrom(uid) {
  return request({
    url: `agent/division/agent/create/${uid}`,
    method: 'get',
  });
}

/**
 * @description 代理商审核
 * @param {Object} param data {Object} 传值参数
 */
export function divisionFrom(id, type) {
  return request({
    url: `agent/division/examine_apply/${id}/${type}`,
    method: 'get',
  });
}

/**
 * @description 事业部添加--表单
 * @param {Object} param data {Object} 传值参数
 */
export function regionFrom(uid) {
  return request({
    url: `agent/division/create/${uid}`,
    method: 'get',
  });
}
/**
 * @description 事业部列表
 * @param {Object} param data {Object} 传值参数
 */
export function clerkList(data) {
  return request({
    url: `agent/division/down_list`,
    method: 'get',
    params: data,
  });
}

/**
 * @description 事业部状态切换--列表
 * @param {Object} param data {Object} 传值参数
 */
export function isShowApi(data) {
  return request({
    url: `agent/division/set_status/${data.status}/${data.id}`,
    method: 'put',
  });
}
