import request from '@/libs/request';

export function couponProductList(params) {
  return request({
    url: 'coupon/product/list',
    method: 'get',
    params,
  });
}

export function couponProductDetail(id) {
  return request({
    url: `coupon/product/${id}`,
    method: 'get',
  });
}

export function couponProductSave(data) {
  return request({
    url: 'coupon/product',
    method: 'post',
    data,
  });
}

export function couponProductUpdate(id, data) {
  return request({
    url: `coupon/product/${id}`,
    method: 'put',
    data,
  });
}

export function couponProductDelete(id) {
  return request({
    url: `coupon/product/${id}`,
    method: 'delete',
  });
}
