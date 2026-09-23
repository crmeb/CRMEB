/**
 * ProductCenter 中台 API
 * 调用后端 ProductCenter 接口获取商品数据
 */
import request from '@/utils/request.js';

const BASE = '';

/**
 * 获取商品列表
 * @param {Object} params { page, limit, keyword, priceOrder, salesOrder, cate_id }
 */
export function getCenterProductList(params) {
  return request.get(`${BASE}/products`, params);
}

/**
 * 获取商品详情
 * @param {number} id 商品ID
 */
export function getCenterProductDetail(id) {
  return request.get(`${BASE}/product/detail/${id}`);
}

/**
 * 获取产品类型列表
 */
export function getProductTypeList() {
  return request.get(`${BASE}/center/product/type_list`);
}
