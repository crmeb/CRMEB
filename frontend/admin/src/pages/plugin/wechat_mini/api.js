/**
 * 微信小程序模块 API（含小程序设置、CI 自动化上传、链接管理、直播）
 */
import request from '@/libs/request';

// ==================== 小程序下载 ====================

/**
 * @description 小程序包下载
 * @param {Object} data {Object} 传值参数
 */
export function routineDownload(data) {
  return request({
    url: 'app/routine/download',
    method: 'post',
    data,
  });
}

/**
 * @description 小程序下载页面数据
 */
export function routineInfo() {
  return request({
    url: 'app/routine/info',
    method: 'get',
  });
}

// ==================== 小程序 CI 自动化上传 ====================

/**
 * @description 获取小程序CI运行环境状态
 */
export function routineCIEnvironment() {
  return request({
    url: 'app/routine/ci/environment',
    method: 'get',
  });
}

/**
 * @description 获取环境安装指南
 */
export function routineCIGuide() {
  return request({
    url: 'app/routine/ci/guide',
    method: 'get',
  });
}

/**
 * @description 获取小程序上传配置
 */
export function routineCIConfig() {
  return request({
    url: 'app/routine/ci/config',
    method: 'get',
  });
}

/**
 * @description 保存小程序上传密钥
 * @param {Object} data { key_content: 密钥内容 }
 */
export function routineCISaveKey(data) {
  return request({
    url: 'app/routine/ci/private_key',
    method: 'post',
    data,
  });
}

/**
 * @description 上传小程序代码
 * @param {Object} data { version: 版本号, desc: 描述, is_live: 是否开启直播 }
 */
export function routineCIUpload(data) {
  return request({
    url: 'app/routine/ci/upload',
    method: 'post',
    data,
  });
}

/**
 * @description 获取小程序预览二维码
 * @param {Object} data { page_path: 预览页面路径 }
 */
export function routineCIPreview(data) {
  return request({
    url: 'app/routine/ci/preview',
    method: 'post',
    data,
  });
}

// ==================== 小程序链接管理 ====================

/**
 * @description 小程序链接 -- 列表
 * @param {Object} data {Object} 传值参数
 */
export function routineSchemeList(data) {
  return request({
    url: 'app/routine/scheme_list',
    method: 'get',
    params: data,
  });
}

/**
 * @description 小程序链接 -- 创建修改表单
 * @param {Number} id 标签id
 */
export function routineSchemeForm(id) {
  return request({
    url: `app/routine/scheme_form/${id}`,
    method: 'get',
  });
}

/**
 * @description 小程序链接 -- 删除
 * @param {Number} id 标签id
 */
export function routineSchemeDel(id) {
  return request({
    url: `app/routine/scheme_del/${id}`,
    method: 'delete',
  });
}

// ==================== 直播 ====================

/**
 * @description 直播列表
 */
export function liveListApi(params) {
  return request({
    url: 'live/room/list',
    method: 'get',
    params,
  });
}

/**
 * @description 添加直播间
 */
export function liveAddApi(data) {
  return request({
    url: 'live/room/add',
    method: 'post',
    data,
  });
}

/**
 * @description 直播间详情
 */
export function liveDetailApi(id) {
  return request({
    url: 'live/room/detail/' + id,
    method: 'get',
  });
}

/**
 * @description 直播间设置是否显示
 */
export function liveShowApi(id, type) {
  return request({
    url: `live/room/set_show/${id}/${type}`,
    method: 'get',
  });
}

/**
 * @description 直播商品列表
 */
export function liveGoodsListApi(params) {
  return request({
    url: 'live/goods/list',
    method: 'get',
    params,
  });
}

/**
 * @description 生成直播商品（选择商品后生成）
 */
export function liveGoodsCreatApi(data) {
  return request({
    url: 'live/goods/create',
    method: 'post',
    data,
  });
}

/**
 * @description 添加直播商品（提交到微信）
 */
export function liveGoodsAddApi(data) {
  return request({
    url: 'live/goods/add',
    method: 'post',
    data,
  });
}

/**
 * @description 直播间添加商品
 */
export function liveRoomGoodsAddApi(data) {
  return request({
    url: 'live/room/add_goods',
    method: 'post',
    data,
  });
}

/**
 * @description 同步直播间
 */
export function liveSyncRoomApi() {
  return request({
    url: 'live/room/syncRoom',
    method: 'get',
  });
}

/**
 * @description 同步商品
 */
export function liveSyncGoodsApi() {
  return request({
    url: 'live/goods/syncGoods',
    method: 'get',
  });
}

/**
 * @description 主播列表
 */
export function liveAnchorListApi(params) {
  return request({
    url: 'live/anchor/list',
    method: 'get',
    params,
  });
}

/**
 * @description 主播添加/修改获取表单
 */
export function liveAnchorAddApi(id) {
  return request({
    url: 'live/anchor/add/' + id,
    method: 'get',
  });
}

/**
 * @description 删除主播
 */
export function liveAnchorDelApi(id) {
  return request({
    url: `live/anchor/del/${id}`,
    method: 'delete',
  });
}

/**
 * @description 直播商品详情
 */
export function liveGoodsDetailApi(id) {
  return request({
    url: 'live/goods/detail/' + id,
    method: 'get',
  });
}

/**
 * @description 直播商品显示
 */
export function liveGoodsShowApi(id, type) {
  return request({
    url: `live/goods/set_show/${id}/${type}`,
    method: 'get',
  });
}
