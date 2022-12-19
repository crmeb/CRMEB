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
 * @description 文章管理--列表
 * @param {Object} param data {Object} 传值参数
 */
export function cmsListApi(data) {
  return request({
    url: 'cms/cms',
    method: 'get',
    params: data,
  });
}

/**
 * @description 文章管理--新增编辑
 * @param {Object} param data {Object} 传值参数
 */
export function cmsAddApi(data) {
  return request({
    url: 'cms/cms',
    method: 'post',
    data,
  });
}

/**
 * @description 文章管理--文章详情
 * @param {Number} param id {Number} 文章id
 */
export function createApi(id) {
  return request({
    url: `cms/cms/${id}`,
    method: 'get',
  });
}

/**
 * @description 文章分类--新增表单
 */
export function categoryAddApi() {
  return request({
    url: `cms/category/create`,
    method: 'GET',
  });
}

/**
 * @description 文章分类--列表
 * @param {Object} param params {Object} 传值
 */
export function categoryListApi(params) {
  return request({
    url: `cms/category`,
    method: 'GET',
    params,
  });
}
/**
 * @description 文章分类--新版列表
 * @param {Object} param params {Object} 传值
 */
export function categoryTreeListApi() {
  return request({
    url: `cms/category_tree_list`,
    method: 'GET',
  });
}

/**
 * @description 文章分类--编辑表单
 * @param {Number} param id {Number} 文章id
 */
export function categoryEditApi(id) {
  return request({
    url: `cms/category/${id}/edit`,
    method: 'GET',
  });
}

/**
 * @description 文章分类--修改状态
 * @param {Object} param data {Object} 传值
 */
export function statusApi(data) {
  return request({
    url: `cms/category/set_status/${data.id}/${data.status}`,
    method: 'put',
  });
}

/**
 * @description 文章分类--关联商品
 * @param {Number} param id {Number} 文章id
 * @param {Object} param data {Object} 传值
 */
export function relationApi(data, id) {
  return request({
    url: `cms/cms/relation/${id}`,
    method: 'put',
    data,
  });
}
