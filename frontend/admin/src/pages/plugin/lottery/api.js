import request from '@/libs/request';

/**
 * 抽奖列表
 */
export function lotteryListApi(data) {
  return request({
    url: 'marketing/lottery/list',
    method: 'get',
    params: data,
  });
}

/**
 * 抽奖活动详情
 */
export function lotteryDetailApi(id) {
  return request({
    url: `marketing/lottery/detail/${id}`,
    method: 'get',
  });
}

/**
 * 创建抽奖
 */
export function lotteryCreateApi(data) {
  return request({
    url: 'marketing/lottery/add',
    method: 'post',
    data,
  });
}

/**
 * 编辑抽奖
 */
export function lotteryEditApi(id, data) {
  return request({
    url: `marketing/lottery/edit/${id}`,
    method: 'put',
    data,
  });
}

/**
 * 删除抽奖
 */
export function lotteryDelApi(id) {
  return request({
    url: `marketing/lottery/del/${id}`,
    method: 'delete',
  });
}

/**
 * 设置状态
 */
export function lotteryStatusApi(data) {
  return request({
    url: `marketing/lottery/set_status/${data.id}/${data.status}`,
    method: 'put',
  });
}

/**
 * 中奖记录
 */
export function lotteryRecordList(data) {
  return request({
    url: 'marketing/lottery/record/list',
    method: 'get',
    params: data,
  });
}

/**
 * 中奖发货处理
 */
export function lotteryRecordDeliver(data) {
  return request({
    url: 'marketing/lottery/record/deliver',
    method: 'post',
    data,
  });
}

/**
 * 抽奖列表（链接地址选择用）
 */
export function lotteryList(data) {
  return request({
    url: 'marketing/lottery/list',
    method: 'get',
    params: data,
  });
}

/**
 * 抽奖状态切换（PUT）
 */
export function lotteryStatus(data) {
  return request({
    url: `marketing/lottery/set_status/${data.id}/${data.status}`,
    method: 'put',
  });
}

/**
 * 抽奖类型列表
 */
export function factorListApi() {
  return request({
    url: 'marketing/lottery/factor/list',
    method: 'get',
  });
}

/**
 * 抽奖配置保存
 */
export function factorUseApi(data) {
  return request({
    url: 'marketing/lottery/factor/use',
    method: 'post',
    data,
  });
}
