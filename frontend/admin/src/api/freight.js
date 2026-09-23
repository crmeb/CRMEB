// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

import request from '@/libs/request';

// ============ 运费模板 API ============

/**
 * 运费模板列表
 */
export function templatesApi(data) {
  return request({
    url: `freight/shipping_templates/list`,
    method: 'get',
    params: data,
  });
}

/**
 * 运费模板 -- 城市数据
 */
export function templatesCityListApi() {
  return request({
    url: `freight/shipping_templates/city_list`,
    method: 'get',
  });
}

/**
 * 运费模板 -- 提交保存
 */
export function templatesSaveApi(id, data) {
  return request({
    url: `freight/shipping_templates/save/${id}`,
    method: 'post',
    data,
  });
}

/**
 * 运费模板 -- 编辑详情
 */
export function shipTemplatesApi(id) {
  return request({
    url: `freight/shipping_templates/${id}/edit`,
    method: 'get',
  });
}
