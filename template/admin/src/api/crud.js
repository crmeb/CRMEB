// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

import request from '@/libs/request';

/**
 * @description 获取接口配置
 * @param {Object} param data {Object} 传值参数
 */
export function crudApi(table_name) {
  return request({
    url: `system/crud/config/${table_name}`,
    method: 'get',
  });
}

/**
 * @description 列表接口
 */
export function getList(url, params) {
  return request({
    url: url,
    method: 'get',
    params,
  });
}
/**
 * @description 创建接口
 */
export function getCreateApi(url) {
  return request({
    url: url,
    method: 'get',
  });
}

export function getStatusApi(url, data) {
  return request({
    url: url,
    method: 'put',
    data,
  });
}
/**
 * @description 创建接口
 */
export function getEditApi(url) {
  return request({
    url: url,
    method: 'get',
  });
}
