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
 * @description 数据字典列表
 */
export function crudDataDictionary(where) {
  return request({
    url: `/system/crud/data_dictionary`,
    method: 'get',
    params: where,
  });
}
/**
 * @description 获取可以进行关联的表名
 */
export function crudAssociationTable() {
  return request({
    url: `/system/crud/association_table`,
    method: 'get',
  });
}
/**
 * @description 获取表的详细信息
 */
export function crudAssociationTableName(tableName) {
  return request({
    url: `/system/crud/association_table/${tableName}`,
    method: 'get',
  });
}
/**
 * @description 查看数据字典
 */
export function crudDataDictionaryList(id) {
  return request({
    url: `/system/crud/data_dictionary/${id}`,
    method: 'get',
  });
}
/**
 * @description 保存数据字典
 */
export function saveCrudDataDictionaryList(id, data) {
  return request({
    url: `/system/crud/data_dictionary/${id}`,
    method: 'post',
    data,
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

/**
 * @description 获取数据字典列表
 */
export function getDataDictionaryList(data) {
  return request({
    url: `/system/crud/data_dictionary_list`,
    method: 'get',
    params: data,
  });
}
/**
 * @description 获取数据字典添加修改表单
 */
export function getDataDictionaryForm(id) {
  return request({
    url: `/system/crud/data_dictionary_list/create/${id}`,
    method: 'get',
  });
}

/**
 * @description 查看数据字典内容列表
 */
export function getDataDictionaryInfoList(data) {
  return request({
    url: `/system/crud/data_dictionary/info_list/${data.id}`,
    method: 'get',
    params: data,
  });
}

/**
 * @description 查看数据字典内容
 */
export function getDataDictionaryInfo(cid, id, pid) {
  return request({
    url: `/system/crud/data_dictionary/info_create/${cid}/${id}/${pid}`,
    method: 'get',
  });
}
