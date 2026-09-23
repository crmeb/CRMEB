import request from '@/libs/request';

export function printerDriverListApi() {
  return request({ url: 'driver/printer_driver/list', method: 'get' });
}

export function printerDriverSyncApi() {
  return request({ url: 'driver/printer_driver/sync', method: 'post' });
}

export function printerDriverDetailApi(id) {
  return request({ url: `driver/printer_driver/detail/${id}`, method: 'get' });
}

export function printerDriverSetStatusApi(id, status) {
  return request({ url: `driver/printer_driver/set_status/${id}`, method: 'put', data: { status } });
}

export function printerDriverSaveConfigApi(id, data) {
  return request({ url: `driver/printer_driver/save_config/${id}`, method: 'post', data });
}
