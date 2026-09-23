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
 * 实物商品列表
 */
export function normalProductList(params) {
  return request({
    url: 'normal/product/list',
    method: 'get',
    params,
  });
}

/**
 * 实物商品各类型数量（tab 头部统计）
 */
export function normalProductTypeHeader() {
  return request({
    url: 'normal/product/type_header',
    method: 'get',
  });
}

/**
 * 实物商品详情
 */
export function normalProductInfo(id) {
  return request({
    url: `normal/product/${id}`,
    method: 'get',
  });
}

/**
 * 保存实物商品（新增）
 */
export function normalProductSave(data) {
  return request({
    url: 'normal/product',
    method: 'POST',
    data,
  });
}

/**
 * 更新实物商品
 */
export function normalProductUpdate(id, data) {
  return request({
    url: `normal/product/${id}`,
    method: 'PUT',
    data,
  });
}

/**
 * 单个上下架
 */
export function normalProductSetStatus(id, status) {
  return request({
    url: `normal/product/status/${id}/${status}`,
    method: 'PUT',
  });
}

/**
 * 批量上下架
 */
export function normalProductBatchStatus(data) {
  return request({
    url: 'normal/product/batch_status',
    method: 'PUT',
    data,
  });
}

/**
 * 批量设置（分类/物流/积分/优惠券/标签/推荐等）
 */
export function normalProductBatchSetting(data) {
  return request({
    url: 'normal/product/batch_setting',
    method: 'POST',
    data,
  });
}

/**
 * 彻底删除商品
 */
export function normalProductFullDel(id) {
  return request({
    url: `normal/product/full_del/${id}`,
    method: 'DELETE',
  });
}

// ============ 配送员管理 API ============

/**
 * 获取所有配送员列表
 */
export function orderDeliveryList() {
  return request({
    url: '/setting/service_personnel/index',
    method: 'get',
  });
}
