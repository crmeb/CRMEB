import request from '@/libs/request';

export function copyDriverListApi(params) {
  return request({
    url: 'driver/copy_driver/list',
    method: 'get',
    params,
  });
}

export function copyDriverSyncApi() {
  return request({
    url: 'driver/copy_driver/sync',
    method: 'post',
  });
}

export function copyDriverSetStatusApi(id, status) {
  return request({
    url: `driver/copy_driver/set_status/${id}`,
    method: 'put',
    data: { status },
  });
}

export function copyDriverDetailApi(id) {
  return request({
    url: `driver/copy_driver/detail/${id}`,
    method: 'get',
  });
}

export function saveCopyDriverConfigApi(id, data) {
  return request({
    url: `driver/copy_driver/save_config/${id}`,
    method: 'post',
    data,
  });
}
