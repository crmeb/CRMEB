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
 * @description 获取消息管理列表数据
 * @param {Object} param params {Object} 传值参数
 */
export function getNotificationList(type) {
  return request({
    url: `setting/notification/index?type=${type}`,
    method: 'get',
  });
}
/**
 * @description 获取消息管理设置数据获取
 * @param {Object} param params {Object} 传值参数
 */
export function getNotificationInfo(id, type) {
  return request({
    url: `setting/notification/info?id=${id}&type=${type}`,
    method: 'get',
  });
}

/**
 * @description 获取消息管理设置数据获取
 * @param {Object} param params {Object} 传值参数
 */
export function getNotificationSave(data) {
  return request({
    url: `setting/notification/save`,
    method: 'post',
    data,
  });
}

/**
 * @description 设置站内消息
 * @param {Number} param id {Number}
 */
export function noticeStatus(type, status, id) {
  return request({
    url: `setting/notification/set_status/${type}/${status}/${id}`,
    method: 'put',
  });
}

/**
 * @description 渠道管理列表
 * @param {Object} param params {Object} 传值参数
 */
export function noticeChannelList(params) {
  return request({
    url: 'setting/notice_channel/list',
    method: 'get',
    params,
  });
}

/**
 * @description 渠道详情
 * @param {Number} param id {Number} 渠道ID
 */
export function noticeChannelDetail(id) {
  return request({
    url: `setting/notice_channel/detail/${id}`,
    method: 'get',
  });
}

/**
 * @description 更新渠道配置
 * @param {Number} param id {Number} 渠道ID
 * @param {Object} param data {Object} 配置数据
 */
export function noticeChannelUpdate(id, data) {
  return request({
    url: `setting/notice_channel/${id}`,
    method: 'put',
    data,
  });
}

/**
 * @description 设置渠道状态
 * @param {Number} param id {Number} 渠道ID
 * @param {Number} param status {Number} 状态
 */
export function noticeChannelSetStatus(id, status) {
  return request({
    url: `setting/notice_channel/set_status/${id}/${status}`,
    method: 'put',
  });
}

/**
 * @description 渠道选项
 * @param {Object} params type: 1=用户端 2=平台端 0=全部
 */
export function noticeChannelOptions(params) {
  return request({
    url: 'setting/notice_channel/options',
    method: 'get',
    params,
  });
}

/**
 * @description 测试渠道发送
 * @param {Number} param id {Number} 渠道ID
 */
export function noticeChannelTest(id, data = {}) {
  return request({
    url: `setting/notice_channel/test/${id}`,
    method: 'post',
    data,
  });
}

/**
 * @description 发送记录列表
 * @param {Object} param params {Object} 传值参数
 */
export function noticeLogList(params) {
  return request({
    url: 'setting/notice_log/list',
    method: 'get',
    params,
  });
}

/**
 * @description 发送记录详情
 * @param {Number} param id {Number} 记录ID
 */
export function noticeLogDetail(id) {
  return request({
    url: `setting/notice_log/detail/${id}`,
    method: 'get',
  });
}

/**
 * @description 发送统计
 * @param {Object} param params {Object} 传值参数
 */
export function noticeLogStatistics(params) {
  return request({
    url: 'setting/notice_log/statistics',
    method: 'get',
    params,
  });
}

/**
 * @description 重试发送
 * @param {Number} param id {Number} 记录ID
 */
export function noticeLogRetry(id) {
  return request({
    url: `setting/notice_log/retry/${id}`,
    method: 'post',
  });
}

/**
 * @description 清理日志
 * @param {Object} param data {Object} 清理参数
 */
export function noticeLogClean(data) {
  return request({
    url: 'setting/notice_log/clean',
    method: 'post',
    data,
  });
}
