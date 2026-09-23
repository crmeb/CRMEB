/**
 * 微信公众号模块 API（微信菜单/自动回复/图文管理/用户标签分组/行为记录/模板消息）
 */
import request from '@/libs/request';

// ==================== 微信菜单 ====================

/**
 * @description 公众号 --公众号配置-- 微信菜单
 * @param {Object} data {Object} 传值参数
 */
export function wechatMenuApi(data) {
  return request({
    url: `app/wechat/menu`,
    method: 'get',
  });
}

/**
 * @description 公众号 --公众号配置-- 微信菜单提交
 * @param {Object} data {Object} 传值参数
 */
export function MenuApi(data) {
  return request({
    url: `app/wechat/menu`,
    method: 'post',
    data,
  });
}

// ==================== 微信模板消息 ====================

/**
 * @description  同步微信模版消息
 */
export function wechatSyncTemplate() {
  return request({
    url: `app/wechat/syncSubscribe`,
    method: 'GET',
  });
}

/**
 * @description 微信模板消息 -- 列表
 * @param {Object} data {Object} 传值参数
 */
export function wechatListApi(data) {
  return request({
    url: 'app/wechat/template',
    method: 'get',
    params: data,
  });
}

/**
 * @description 微信模板消息 -- 新增表单
 */
export function wechatCreateApi() {
  return request({
    url: 'app/wechat/template/create',
    method: 'get',
  });
}

/**
 * @description 微信模板消息 -- 编辑表单
 * @param {Number} id 模板id
 */
export function wechatEditApi(id) {
  return request({
    url: `app/wechat/template/${id}/edit`,
    method: 'get',
  });
}

/**
 * @description 微信模板消息 -- 修改状态
 * @param {Object} data {Object} 传值参数
 */
export function wechatSetStatusApi(data) {
  return request({
    url: `app/wechat/template/set_status/${data.id}/${data.status}`,
    method: 'PUT',
  });
}

// ==================== 自动回复 ====================

/**
 * @description  自动回复 -- 关注回复 关键字回复 保存
 * @param {Object} data {Object} 传值参数
 */
export function replyApi(data) {
  return request({
    url: data.url,
    method: 'post',
    data: data.key,
  });
}

/**
 * @description  自动回复 -- 关键字 列表
 * @param {Object} params {Object} 传值参数
 */
export function keywordListApi(params) {
  return request({
    url: `app/wechat/keyword`,
    method: 'get',
    params,
  });
}

/**
 * @description  自动回复 -- 关键字 修改状态
 * @param {Object} data {Object} 传值参数
 */
export function keywordsetStatusApi(data) {
  return request({
    url: `app/wechat/keyword/set_status/${data.id}/${data.status}`,
    method: 'PUT',
  });
}

/**
 * @description  自动回复 -- 详情
 * @param {String} url 请求地址
 * @param {Object} data 传值参数
 */
export function keywordsinfoApi(url, data) {
  return request({
    url: url,
    method: 'get',
    params: data.key,
  });
}

// ==================== 图文管理 ====================

/**
 * @description  图文管理 -- 新增
 * @param {Object} data {Object} 传值参数
 */
export function wechatNewsAddApi(data) {
  return request({
    url: `/app/wechat/news`,
    method: 'POST',
    data,
  });
}

/**
 * @description  图文管理 -- 列表
 * @param {Object} params {Object} 传值参数
 */
export function wechatNewsListApi(params) {
  return request({
    url: `app/wechat/news`,
    method: 'GET',
    params,
  });
}

/**
 * @description  图文管理 -- 详情
 * @param {Number} id 图文id
 */
export function wechatNewsInfotApi(id) {
  return request({
    url: `app/wechat/news/${id}`,
    method: 'GET',
  });
}

/**
 * @description  图文管理 -- 发送图文
 * @param {Object} data {Object} 传值参数
 */
export function wechatPushApi(data) {
  return request({
    url: `app/wechat/push`,
    method: 'POST',
    data,
  });
}

// ==================== 微信用户 ====================

/**
 * @description  微信用户 -- 列表
 * @param {Object} params {Object} 传值参数
 */
export function wechatUserListtApi(params) {
  return request({
    url: `app/wechat/user`,
    method: 'GET',
    params,
  });
}

/**
 * @description  微信用户 -- 用户分组和标签
 */
export function tagListtApi() {
  return request({
    url: `app/wechat/user/tag_group`,
    method: 'GET',
  });
}

/**
 * @description  微信用户 -- 用户分组和标签编辑
 * @param {String} url {String} 请求地址
 */
export function groupsEditApi(url) {
  return request({
    url: url,
    method: 'GET',
  });
}

// ==================== 用户标签 ====================

/**
 * @description  用户标签 -- 列表
 */
export function wechatTagListApi() {
  return request({
    url: `app/wechat/tag`,
    method: 'GET',
  });
}

/**
 * @description  用户标签 -- 添加表单
 */
export function wechatTagCreateApi() {
  return request({
    url: `app/wechat/tag/create`,
    method: 'GET',
  });
}

/**
 * @description  用户标签 -- 编辑表单
 * @param {Number} id 标签id
 */
export function wechatTagEditApi(id) {
  return request({
    url: `app/wechat/tag/${id}/edit`,
    method: 'GET',
  });
}

// ==================== 用户分组 ====================

/**
 * @description  用户分组 -- 列表
 */
export function wechatGroupListApi() {
  return request({
    url: `app/wechat/group`,
    method: 'GET',
  });
}

/**
 * @description  用户分组 -- 添加表单
 */
export function wechatGroupCreateApi() {
  return request({
    url: `app/wechat/group/create`,
    method: 'GET',
  });
}

/**
 * @description  用户分组 -- 编辑表单
 * @param {Number} id 分组id
 */
export function wechatGroupEditApi(id) {
  return request({
    url: `app/wechat/group/${id}/edit`,
    method: 'GET',
  });
}

// ==================== 用户行为记录 ====================

/**
 * @description  用户行为 -- 列表
 * @param {Object} params {Object} 传值参数
 */
export function wechatActionListApi(params) {
  return request({
    url: `app/wechat/action`,
    method: 'GET',
    params,
  });
}

// ==================== 其他 ====================

/**
 * @description 下载二维码
 * @param {Number} id 回复id
 */
export function downloadReplyCode(id) {
  return request({
    url: `app/wechat/code_reply/${id}`,
    method: 'GET',
  });
}
