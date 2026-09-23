import request from '@/libs/request';

export function orderCenterList(data) {
  return request({
    url: '/order/center/list',
    method: 'get',
    params: data,
  });
}

export function orderCenterStat(data) {
  return request({
    url: '/order/center/stat',
    method: 'get',
    params: data,
  });
}

export function orderCenterDetail(id) {
  return request({
    url: `/order/center/detail/${id}`,
    method: 'get',
  });
}

export function orderCenterRemark(data) {
  return request({
    url: '/order/center/remark',
    method: 'put',
    data,
  });
}

export function orderCenterDelete(id) {
  return request({
    url: '/order/center/delete',
    method: 'delete',
    params: { id },
  });
}

export function orderCenterCategories() {
  return request({
    url: '/order/center/categories',
    method: 'get',
  });
}

export function orderCenterFilterOptions() {
  return request({
    url: '/order/center/filter_options',
    method: 'get',
  });
}
