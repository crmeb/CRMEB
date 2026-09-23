import request from '@/libs/request';

/**
 * 获取物流公司列表
 * @param {Object} params
 */
export function companyListApi(params) {
  return request({
    url: '/driver/express_company/list',
    method: 'get',
    params,
  });
}

/**
 * 获取物流公司详情
 * @param {number} id
 */
export function companyReadApi(id) {
  return request({
    url: `/driver/express_company/read/${id}`,
    method: 'get',
  });
}

/**
 * 新增物流公司
 * @param {Object} data
 */
export function companySaveApi(data) {
  return request({
    url: '/driver/express_company/save',
    method: 'post',
    data,
  });
}

/**
 * 修改物流公司
 * @param {number} id
 * @param {Object} data
 */
export function companyUpdateApi(id, data) {
  return request({
    url: `/driver/express_company/update/${id}`,
    method: 'put',
    data,
  });
}

/**
 * 删除物流公司
 * @param {number} id
 */
export function companyDeleteApi(id) {
  return request({
    url: `/driver/express_company/delete/${id}`,
    method: 'delete',
  });
}

/**
 * 修改状态
 * @param {number} id
 * @param {number} status
 */
export function companySetStatusApi(id, status) {
  return request({
    url: `/driver/express_company/set_status/${id}/${status}`,
    method: 'put',
  });
}

/**
 * 修改展示
 * @param {number} id
 * @param {number} isShow
 */
export function companySetIsShowApi(id, isShow) {
  return request({
    url: `/driver/express_company/set_is_show/${id}/${isShow}`,
    method: 'put',
  });
}

/**
 * 排序
 * @param {Object} data
 */
export function companySortApi(data) {
  return request({
    url: '/driver/express_company/sort',
    method: 'put',
    data,
  });
}

/**
 * 获取驱动列表下拉
 */
export function companyDriverOptionsApi() {
  return request({
    url: '/driver/express_company/driver_options',
    method: 'get',
  });
}
