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

// ==================== 电子面单模板管理 ====================

/**
 * @description 电子面单模板 列表
 * @param {Object} params 传值参数
 */
export function expressTemplateListApi(params) {
  return request({
    url: 'driver/express_template/list',
    method: 'get',
    params,
  });
}

/**
 * @description 电子面单模板 同步（从一号通拉取）
 * @param {Object} data { com, com_name }
 */
export function expressTemplateSyncApi(data) {
  return request({
    url: 'driver/express_template/sync',
    method: 'post',
    data,
  });
}

/**
 * @description 电子面单模板 详情
 * @param {Number} id 模板ID
 */
export function expressTemplateDetailApi(id) {
  return request({
    url: `driver/express_template/detail/${id}`,
    method: 'get',
  });
}

/**
 * @description 电子面单模板 修改状态
 * @param {Number} id 模板ID
 * @param {Number} status 状态
 */
export function expressTemplateSetStatusApi(id, status) {
  return request({
    url: `driver/express_template/set_status/${id}`,
    method: 'put',
    data: { status },
  });
}

/**
 * @description 电子面单模板 更新
 * @param {Number} id 模板ID
 * @param {Object} data 更新数据
 */
export function expressTemplateUpdateApi(id, data) {
  return request({
    url: `driver/express_template/${id}`,
    method: 'put',
    data,
  });
}

/**
 * @description 电子面单模板 删除
 * @param {Number} id 模板ID
 */
export function expressTemplateDeleteApi(id) {
  return request({
    url: `driver/express_template/${id}`,
    method: 'delete',
  });
}

// ==================== 电子面单配置 ====================

/**
 * @description 获取电子面单配置
 */
export function getExpressConfigApi() {
  return request({
    url: 'driver/express_template/config',
    method: 'get',
  });
}

/**
 * @description 保存电子面单配置
 * @param {Object} data 配置数据
 */
export function saveExpressConfigApi(data) {
  return request({
    url: 'driver/express_template/config',
    method: 'post',
    data,
  });
}

/**
 * @description 检查电子面单可用性
 */
export function checkExpressAvailableApi() {
  return request({
    url: 'driver/express_template/check',
    method: 'get',
  });
}

// ==================== 打印记录 ====================

/**
 * @description 电子面单打印记录 列表
 * @param {Object} params 传值参数
 */
export function expressDumpLogListApi(params) {
  return request({
    url: 'driver/express_template/dump_log/list',
    method: 'get',
    params,
  });
}

/**
 * @description 电子面单打印记录 详情
 * @param {Number} id 记录ID
 */
export function expressDumpLogDetailApi(id) {
  return request({
    url: `driver/express_template/dump_log/${id}`,
    method: 'get',
  });
}
