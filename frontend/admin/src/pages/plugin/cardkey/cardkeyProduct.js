import request from '@/libs/request';

export function cardkeyProductList(params) {
  return request({
    url: 'cardkey/product/list',
    method: 'get',
    params,
  });
}

export function cardkeyProductDetail(id) {
  return request({
    url: `cardkey/product/${id}`,
    method: 'get',
  });
}

export function cardkeyProductSave(data) {
  return request({
    url: 'cardkey/product',
    method: 'post',
    data,
  });
}

export function cardkeyProductUpdate(id, data) {
  return request({
    url: `cardkey/product/${id}`,
    method: 'put',
    data,
  });
}

export function cardkeyProductDelete(id) {
  return request({
    url: `cardkey/product/${id}`,
    method: 'delete',
  });
}

export function cardkeyProductCardList(id) {
  return request({
    url: `cardkey/product/${id}/card_list`,
    method: 'get',
  });
}

export function cardkeyProductImportCard(id, cards) {
  return request({
    url: `cardkey/product/${id}/import_card`,
    method: 'post',
    data: { cards },
  });
}

export function cardkeyOrderList(params) {
  return request({
    url: 'cardkey/order/list',
    method: 'get',
    params,
  });
}

export function cardkeyOrderDetail(orderNo) {
  return request({
    url: `cardkey/order/${orderNo}`,
    method: 'get',
  });
}
