import request from '@/libs/request';

export function normalStatisticBasic(params) {
  return request({ url: 'normal/statistic/get_basic', method: 'get', params });
}

export function normalStatisticTrend(params) {
  return request({ url: 'normal/statistic/get_trend', method: 'get', params });
}

export function normalStatisticRanking(params) {
  return request({ url: 'normal/statistic/get_product_ranking', method: 'get', params });
}

export function normalStatisticExcel(params) {
  return request({ url: 'normal/statistic/get_excel', method: 'get', params });
}
