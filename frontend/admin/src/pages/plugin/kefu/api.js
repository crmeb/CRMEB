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

/*
 * 登录
 * */
export function AccountLogin(data) {
  return request({
    url: '/kefu/login',
    method: 'post',
    data,
    kefu: true,
  });
}

/**
 * 获取左侧客服会话列表（会话列表/会话结束，用户与游客混排）
 * @param {Object} params nickname-搜索关键字 is_end-0=会话列表 1=会话结束
 * @constructor
 */
export function record(params) {
  return request({
    url: '/kefu/user/record',
    method: 'get',
    params,
    kefu: true,
  });
}

/**
 * 结束会话：移入「会话结束」列表，不再计入未读
 * @param {Number} toUid 会话对方uid（用户或游客）
 * @constructor
 */
export function endChatApi(toUid) {
  return request({
    url: `/kefu/user/end_chat/${toUid}`,
    method: 'put',
    kefu: true,
  });
}

/**
 * 会话列表（用户+游客）未读消息总数
 * @constructor
 */
export function unreadNumApi() {
  return request({
    url: '/kefu/user/unread_num',
    method: 'get',
    kefu: true,
  });
}

/**
 * 获取左侧用户详情
 * @constructor
 */
export function userInfo(id) {
  return request({
    url: '/kefu/user/info/' + id,
    method: 'get',
    kefu: true,
  });
}

/**
 * 客服话术
 * @constructor
 */
export function speeChcraft(params) {
  return request({
    url: '/kefu/service/speechcraft',
    method: 'get',
    params,
    kefu: true,
  });
}

/**
 * 客服转接列表
 * @constructor
 */
export function transferList(params) {
  return request({
    url: '/kefu/service/transfer_list',
    method: 'get',
    params,
    kefu: true,
  });
}

/**
 * 客服转接列表
 * @constructor
 */
export function serviceTransfer(params) {
  return request({
    url: '/kefu/service/transfer',
    method: 'post',
    params,
    kefu: true,
  });
}

/**
 * 客服用户标签
 * @constructor
 */
export function userLabel(id) {
  return request({
    url: `/kefu/user/label/${id}`,
    method: 'get',
    kefu: true,
  });
}

/**
 * 客服用户标签更新
 * @constructor
 */
export function userLabelPut(id, data) {
  return request({
    url: `/kefu/user/label/${id}`,
    method: 'put',
    data,
    kefu: true,
  });
}

/**
 * 客服用户聊天列表
 * @constructor
 */
export function serviceList(params) {
  return request({
    url: `/kefu/service/list`,
    method: 'get',
    params,
    kefu: true,
  });
}

/**
 * 退出登录
 * @constructor
 */
export function AccountLogoutKefu() {
  return request({
    url: `/kefu/user/logout`,
    method: 'post',
    kefu: true,
  });
}

/**
 * 获取扫码登录凭证
 * @constructor
 */
export function getSanCodeKey() {
  return request({
    url: `/kefu/key`,
    method: 'get',
    kefu: true,
  });
}

/**
 * 获取轮播图和logo
 */
export function loginInfoApi() {
  return request({
    url: '/kefu/config',
    method: 'get',
    kefu: true,
  });
}

/**
 * 获取话术分类
 */
export function serviceCate(params) {
  return request({
    url: '/kefu/service/cate',
    method: 'get',
    params,
    kefu: true,
  });
}

/**
 * 修改话术
 */
export function serviceCateUpdate(id, params) {
  return request({
    url: '/kefu/service/speechcraft/' + id,
    method: 'PUT',
    params,
    kefu: true,
  });
}

/**
 * 添加话术
 */
export function addSpeeChcraft(data) {
  return request({
    url: '/kefu/service/speechcraft',
    method: 'post',
    data,
    kefu: true,
  });
}

/**
 * 添加分类
 */
export function addServiceCate(data) {
  return request({
    url: '/kefu/service/cate',
    method: 'post',
    data,
    kefu: true,
  });
}

/**
 * 修改分类
 */
export function editServiceCate(id, params) {
  return request({
    url: '/kefu/service/cate/' + id,
    method: 'PUT',
    params,
    kefu: true,
  });
}

/**
 * 扫码登录情况
 */
export function scanStatus(key, params) {
  return request({
    url: '/kefu/scan/' + key,
    method: 'get',
    params,
    kefu: true,
  });
}

/**
 * 客服用户分组
 * @constructor
 */
export function userGroupApi() {
  return request({
    url: `/kefu/user/group`,
    method: 'get',
    kefu: true,
  });
}

/**
 * 客服设置用户分组
 * @constructor
 */
export function putGroupApi(uid, id) {
  return request({
    url: `/kefu/user/group/${uid}/${id}`,
    method: 'put',
    kefu: true,
  });
}

/**
 * 客服配置
 * @constructor
 */
export function kefuConfig() {
  return request({
    url: `/kefu/config`,
    method: 'get',
    kefu: true,
  });
}

/**
 * 客户端 随机客服
 * @constructor
 */
export function serviceListApi(params) {
  return request({
    url: `kefu/tourist/user`,
    method: 'get',
    params,
    kefu: true,
  });
}

/**
 * 客户端 广告位
 * @constructor
 */
export function getAdvApi() {
  return request({
    url: `kefu/tourist/adv`,
    method: 'get',
    kefu: true,
  });
}

/**
 * 客户端 聊天记录
 * @constructor
 */
export function chatListApi(params) {
  return request({
    url: `kefu/tourist/chat`,
    method: 'get',
    params,
    kefu: true,
  });
}

/**
 * 客户端 客服反馈
 * @constructor
 */
export function feedbackDataApi() {
  return request({
    url: `kefu/tourist/feedback`,
    method: 'get',
    kefu: true,
  });
}

/**
 * 客户端 反馈提示语
 * @constructor
 */
export function feedbackFromApi(data) {
  return request({
    url: `kefu/tourist/feedback`,
    method: 'post',
    data,
    kefu: true,
  });
}

/**
 * 客服链接获取
 * @constructor
 */
export function getWorkermanUrl() {
  return request({
    url: `get_workerman_url`,
    method: 'get',
  });
}

/**
 * 复制粘贴上传图片
 */
export function uploadImg(data) {
  return request({
    url: `kefu/upload`,
    method: 'post',
    data,
    kefu: true,
  });
}
