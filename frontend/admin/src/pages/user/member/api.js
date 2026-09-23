/**
 * 会员模块 API
 */
import request from '@/libs/request';

// ===== 会员类型 =====

/**
 * 会员类型列表
 */
export function userMemberShip(params) {
  return request({
    url: 'user/member/ship',
    method: 'GET',
    params,
  });
}

/**
 * 保存会员类型
 */
export function memberShipSave(id, data) {
  return request({
    url: `user/member_ship/save/${id}`,
    method: 'POST',
    data,
  });
}

/**
 * 会员卡（修改状态）
 */
export function memberCard(data) {
  return request({
    url: 'user/member_card/set_status',
    method: 'GET',
    params: data,
  });
}

/**
 * 删除会员类型
 */
export function deleteCard(id) {
  return request({
    url: `user/member_ship/delete/${id}`,
    method: 'DELETE',
  });
}

// ===== 卡密会员（批次） =====

/**
 * 会员卡批次列表
 */
export function userMemberBatch(params) {
  return request({
    url: 'user/member_batch/index',
    method: 'GET',
    params,
  });
}

/**
 * 保存会员卡批次
 */
export function memberBatchSave(id, data) {
  return request({
    url: `user/member_batch/save/${id}`,
    method: 'POST',
    data,
  });
}

/**
 * 会员卡批次快速修改
 */
export function memberBatchSetValue(id, data) {
  return request({
    url: `user/member_batch/set_value/${id}`,
    method: 'GET',
    params: data,
  });
}

/**
 * 查看批次下会员卡列表
 */
export function userMemberCard(batchId, params) {
  return request({
    url: `user/member_card/index/${batchId}`,
    method: 'GET',
    params,
  });
}

/**
 * 修改会员卡状态
 */
export function memberCardStatus(data) {
  return request({
    url: 'user/member_card/set_status',
    method: 'GET',
    params: data,
  });
}

/**
 * 会员卡兑换二维码
 */
export function userMemberScan() {
  return request({
    url: 'user/member_scan',
    method: 'GET',
  });
}

// ===== 会员记录 =====

/**
 * 会员购买记录
 */
export function memberRecord(params) {
  return request({
    url: 'user/member/record',
    method: 'GET',
    params,
  });
}

// ===== 会员权益 =====

/**
 * 会员权益列表
 */
export function memberRight() {
  return request({
    url: 'user/member/right',
    method: 'GET',
  });
}

/**
 * 保存会员权益
 */
export function memberRightSave(data) {
  return request({
    url: 'user/member_right/save/0',
    method: 'POST',
    data,
  });
}

// ===== 会员协议 =====

/**
 * 获取会员协议
 */
export function memberAgreement() {
  return request({
    url: 'user/member/agreement',
    method: 'GET',
  });
}

/**
 * 保存会员协议
 */
export function memberAgreementSave(id, data) {
  return request({
    url: `user/member_agreement/save/${id}`,
    method: 'POST',
    data,
  });
}

// ===== 线下收银 =====

/**
 * 线下收银订单列表
 */
export function orderScanList(params) {
  return request({
    url: 'user/scan_list',
    method: 'GET',
    params,
  });
}

/**
 * 线下收款二维码
 */
export function orderOfflineScan(params) {
  return request({
    url: 'user/offline_scan',
    method: 'GET',
    params,
  });
}
