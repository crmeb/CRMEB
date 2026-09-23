/**
 * 文件管理插件 - API
 */
import request from '@/libs/request';

/**
 * @description 文件校验 -- 列表
 */
export function fileListApi() {
  return request({
    url: `system/file`,
    method: 'GET',
  });
}

/**
 * @description 文件管理 -- 登录
 */
export function opendirLoginApi(data) {
  return request({
    url: `system/file/login`,
    method: 'POST',
    data,
  });
}

/**
 * @description 文件管理 -- 列表
 */
export function opendirListApi(params) {
  return request({
    url: `system/file/opendir`,
    method: 'GET',
    params,
    file_edit: true,
  });
}

/**
 * @description 文件管理 -- 读取文件
 */
export function openfileApi(params) {
  return request({
    url: `system/file/openfile`,
    method: 'GET',
    params,
    file_edit: true,
  });
}

/**
 * @description 文件管理 -- 保存
 */
export function savefileApi(data) {
  return request({
    url: `system/file/savefile?fileToken=${data.fileToken}`,
    method: 'post',
    data,
    file_edit: true,
  });
}
/**
 * @description 文件管理 -- 新建文件夹
 */
export function createFolder(params) {
  return request({
    url: `system/file/createFolder`,
    method: 'GET',
    params,
    file_edit: true,
  });
}
/**
 * @description 文件管理 -- 新建文件
 */
export function createFile(params) {
  return request({
    url: `system/file/createFile`,
    method: 'GET',
    params,
    file_edit: true,
  });
}
/**
 * @description 文件管理 -- 文件重命名
 */
export function rename(params) {
  return request({
    url: `system/file/rename`,
    method: 'GET',
    params,
    file_edit: true,
  });
}
/**
 * @description 文件管理 -- 删除文件或文件夹
 */
export function delFolder(params) {
  return request({
    url: `system/file/delFolder`,
    method: 'GET',
    params,
    file_edit: true,
  });
}

/**
 * 文件备注
 * @param {*} params
 * @returns
 */
export function fileMark(params) {
  return request({
    url: `system/file/mark`,
    method: 'get',
    params,
    file_edit: true,
  });
}

/**
 * 文件管理 更新备注
 * @param {*} fileToken
 * @param {*} data
 * @returns
 */
export function markSave(fileToken, data) {
  return request({
    url: `system/file/mark/save?fileToken=${fileToken}`,
    method: 'post',
    data,
  });
}
