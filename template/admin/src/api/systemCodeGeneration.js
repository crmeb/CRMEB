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
 * @description 代码生成 - 菜单选择列表
 */
export function crudMenus() {
  return request({
    url: '/system/crud/menus',
    method: 'get',
  });
}
/**
 * @description 代码生成 - sql表选择列表
 */
export function crudColumnType() {
  return request({
    url: '/system/crud/column_type',
    method: 'get',
  });
}
/**
 * @description 代码生成 - 第一步提交
 */
export function crudFilePath(data) {
  return request({
    url: '/system/crud/file_path',
    method: 'post',
    data,
  });
}

/**
 * @description 代码生成 - 列表
 */
export function crudList(data) {
  return request({
    url: '/system/crud',
    method: 'get',
    params: data,
  });
}
/**
 * @description 代码生成 - 列表查看文件
 */
export function crudDet(id) {
  return request({
    url: `/system/crud/${id}`,
    method: 'get',
  });
}

/**
 * @description 代码生成 - 下载
 */
export function crudDownload(id) {
  return request({
    url: `/system/crud/download/${id}`,
    method: 'get',
  });
}
/**
 * @description 代码生成 - 文件编辑
 */
export function crudSaveFile(id, data) {
  return request({
    url: `/system/crud/save_file/${id}`,
    method: 'post',
    data,
  });
}
