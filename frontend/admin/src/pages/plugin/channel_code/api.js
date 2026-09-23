/**
 * 渠道码模块 API
 */
import request from '@/libs/request';

/**
 * @description 渠道码分类列表
 */
export function wechatQrcodeTree() {
  return request({
    url: `marketing/channel_code/cate/list`,
    method: 'get',
  });
}

/**
 * @description 获取渠道码创建、编辑分类表单
 */
export function wechatQrcodeCreate(id) {
  return request({
    url: `marketing/channel_code/cate/create/${id}`,
    method: 'get',
  });
}

/**
 * @description 获取渠道码列表
 */
export function wechatQrcodeList(params) {
  return request({
    url: `marketing/channel_code/list`,
    method: 'GET',
    params,
  });
}

/**
 * @description 渠道码修改状态
 */
export function wechatQrcodeStatusApi(data) {
  return request({
    url: `marketing/channel_code/set_status/${data.id}/${data.status}`,
    method: 'PUT',
  });
}

/**
 * @description 获取渠道码用户列表
 */
export function getUserList(params) {
  return request({
    url: `marketing/channel_code/user_list/${params.id}`,
    method: 'get',
    params,
  });
}

/**
 * @description 获取渠道码详情
 */
export function wechatQrcodeDetail(id) {
  return request({
    url: `marketing/channel_code/info/${id}`,
    method: 'get',
  });
}

/**
 * @description 渠道码保存
 */
export function wechatQrcodeSaveApi(id, data) {
  return request({
    url: `marketing/channel_code/save/${id}`,
    method: 'POST',
    data,
  });
}

/**
 * @description 渠道码统计
 */
export function wechatQrcodeStatistic(id, params) {
  return request({
    url: `marketing/channel_code/statistic/${id}`,
    method: 'GET',
    params,
  });
}

/**
 * @description 删除渠道码分类
 */
export function wechatQrcodeCateDelApi(id) {
  return request({
    url: `marketing/channel_code/cate/del/${id}`,
    method: 'DELETE',
  });
}

/**
 * @description 删除渠道码
 */
export function wechatQrcodeDelApi(id) {
  return request({
    url: `marketing/channel_code/del/${id}`,
    method: 'DELETE',
  });
}
