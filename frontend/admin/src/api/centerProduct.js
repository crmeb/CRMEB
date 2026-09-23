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
 * 获取产品类型列表
 */
export function centerProductTypeList() {
  return request({
    url: 'center/product/type_list',
    method: 'get',
  });
}

/**
 * 统一商品列表
 */
export function centerProductList(params) {
  return request({
    url: 'center/product/list',
    method: 'get',
    params,
  });
}

/**
 * 商品搜索
 */
export function centerProductSearch(params) {
  return request({
    url: 'center/product/search',
    method: 'get',
    params,
  });
}

/**
 * 商品详情
 */
export function centerProductDetail(id) {
  return request({
    url: `center/product/detail/${id}`,
    method: 'get',
  });
}
