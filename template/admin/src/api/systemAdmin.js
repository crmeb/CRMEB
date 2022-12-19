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
 * @description 列表
 * @param {Object} param data {Object} 传值参数
 */
export function adminListApi(data) {
  return request({
    url: '/setting/admin',
    method: 'get',
    params: data,
  });
}

/**
 * @description 管理员添加表单
 */
export function adminFromApi() {
  return request({
    url: '/setting/admin/create',
    method: 'get',
  });
}

/**
 * @description 管理员编辑表单
 * @param {Number} param id {Number} 管理员id
 */
export function adminEditFromApi(id) {
  return request({
    url: `/setting/admin/${id}/edit`,
    method: 'get',
  });
}

/**
 * @description 管理员删除
 * @param {Number} param id {Number} 管理员id
 */
export function adminDelFromApi(id) {
  return request({
    url: `/setting/admin/${id}`,
    method: 'DELETE',
  });
}

/**
 * @description 管理员 修改状态
 * @param {Object} param data {Object} 传值
 */
export function setShowApi(data) {
  return request({
    url: `setting/set_status/${data.id}/${data.status}`,
    method: 'PUT',
  });
}
