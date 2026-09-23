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
 * 获取服务人员列表
 */
export function getServicePersonnelList(params) {
  return request({
    url: '/setting/service_personnel/index',
    method: 'get',
    params,
  });
}

/**
 * 新增服务人员表单
 */
export function getServicePersonnelAddForm() {
  return request({
    url: '/setting/service_personnel/add',
    method: 'get',
  });
}

/**
 * 保存服务人员
 */
export function saveServicePersonnel(data) {
  return request({
    url: '/setting/service_personnel/save',
    method: 'post',
    data,
  });
}

/**
 * 编辑服务人员表单
 */
export function getServicePersonnelEditForm(id) {
  return request({
    url: `/setting/service_personnel/${id}/edit`,
    method: 'get',
  });
}

/**
 * 修改服务人员
 */
export function updateServicePersonnel(id, data) {
  return request({
    url: `/setting/service_personnel/${id}`,
    method: 'put',
    data,
  });
}

/**
 * 删除服务人员
 */
export function deleteServicePersonnel(id) {
  return request({
    url: `/setting/service_personnel/del/${id}`,
    method: 'delete',
  });
}

/**
 * 修改服务人员状态
 */
export function setServicePersonnelStatus(data) {
  return request({
    url: `/setting/service_personnel/set_status/${data.id}/${data.status}`,
    method: 'put',
  });
}

/**
 * 订单时获取所有服务人员列表
 */
export function orderServicePersonnelList() {
  return request({
    url: '/setting/service_personnel/index',
    method: 'get',
  });
}

/**
 * 获取所有服务人员列表（不分页）
 */
export function getAllServicePersonnel() {
  return request({
    url: '/setting/service_personnel/index',
    method: 'get',
  });
}
