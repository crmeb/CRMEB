// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

import request from '@/libs/request';

/**
 * 同步路由权限
 */
export function syncRoute(appName) {
  return request({
    url: `system/route/sync_route/${appName}`,
    method: 'get',
  });
}
/**
 * 新增路由分类
 */
export function routeCate(appName) {
  return request({
    url: `system/route_cate/create?app_name=${appName}`,
    method: 'get',
  });
}
/**
 * 路由树
 */
export function routeList(apiType) {
  return request({
    url: `system/route/tree?app_name=${apiType}`,
    method: 'get',
  });
}

/**
 * 添加/编辑接口
 * @param {*} data
 * @returns
 */
export function routeSave(data) {
  return request({
    url: `system/route/${data.id}`,
    method: 'post',
    data,
  });
}

/**
 * 接口信息详情
 * @param {*} data
 * @returns
 */
export function routeDet(id) {
  return request({
    url: `system/route/${id}`,
    method: 'get',
  });
}
/**
 * 接口分类编辑
 * @param {*} data
 * @returns
 */
export function routeEdit(id, appName) {
  return request({
    url: `system/route_cate/${id}/edit?app_name=${appName}`,
    method: 'get',
  });
}

/**
 * @description 修改名称
 * @param {Object} data data {Object} 传值
 */
export function interfaceEditName(data) {
  return request({
    url: `setting/system_out_interface/edit_name`,
    method: 'PUT',
    data,
  });
}

/**
 * @description 删除
 */
export function routeDel(id) {
  return request({
    url: 'system/route/' + id,
    method: 'delete',
  });
}
/**
 * @description 删除
 */
export function routeCateDel(id) {
  return request({
    url: 'system/route_cate/' + id,
    method: 'delete',
  });
}

/**
 * 接口信息详情
 * @param {*} data
 * @returns
 */
export function textOutUrl(data) {
  return request({
    url: `setting/system_out_account/text_out_url`,
    method: 'post',
    data,
  });
}
