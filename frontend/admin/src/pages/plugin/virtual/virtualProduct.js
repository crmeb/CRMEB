import request from '@/libs/request';

export function virtualProductList(params) {
  return request({
    url: 'virtual/product/list',
    method: 'get',
    params,
  });
}

export function virtualProductDetail(id) {
  return request({
    url: `virtual/product/${id}`,
    method: 'get',
  });
}

export function virtualProductSave(data) {
  return request({
    url: 'virtual/product',
    method: 'post',
    data,
  });
}

export function virtualProductUpdate(id, data) {
  return request({
    url: `virtual/product/${id}`,
    method: 'put',
    data,
  });
}

export function virtualProductDelete(id) {
  return request({
    url: `virtual/product/${id}`,
    method: 'delete',
  });
}
