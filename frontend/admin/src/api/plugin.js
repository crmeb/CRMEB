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

/**
 * @description 插件列表
 * @param {Object} params { type: 'product|activity|channel|distribution' }
 */
export function pluginListApi(params) {
  return request({
    url: 'plugin/list',
    method: 'get',
    params,
  });
}

/**
 * @description 安装插件
 * @param {Object} data { plugin_key: 'normal' }
 */
export function pluginInstallApi(data) {
  return request({
    url: 'plugin/install',
    method: 'post',
    data,
  });
}

/**
 * @description 卸载插件
 * @param {Object} data { plugin_key: 'normal' }
 */
export function pluginUninstallApi(data) {
  return request({
    url: 'plugin/uninstall',
    method: 'post',
    data,
  });
}

/**
 * @description 启用插件
 * @param {Object} data { plugin_key: 'normal' }
 */
export function pluginEnableApi(data) {
  return request({
    url: 'plugin/enable',
    method: 'post',
    data,
  });
}

/**
 * @description 禁用插件
 * @param {Object} data { plugin_key: 'normal' }
 */
export function pluginDisableApi(data) {
  return request({
    url: 'plugin/disable',
    method: 'post',
    data,
  });
}

/**
 * @description 上传插件 ZIP 包
 * @param {FormData} formData 包含 plugin_package 字段的 FormData
 */
export function pluginUploadApi(formData) {
  return request({
    url: 'plugin/upload',
    method: 'post',
    data: formData,
    headers: { 'Content-Type': 'multipart/form-data' },
  });
}
