// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
import request from '@/libs/request';

/**
 * @description 九宫格抽奖 -- 列表
 */
export function lotteryListApi(data) {
  return request({
    url: 'marketing/lottery/list',
    method: 'get',
    params: data,
  });
}

/**
 * @description 九宫格抽奖 -- 详情
 * @param id 抽奖活动id
 */
export function lotteryDetailApi(id) {
  return request({
    url: `marketing/lottery/detail/${id}`,
    method: 'get',
  });
}

/**
 * @description 九宫格抽奖 -- 新版详情
 * @param id 抽奖活动id
 */
export function lotteryNewDetailApi(type) {
  return request({
    url: `marketing/lottery/factor_info/${type}`,
    method: 'get',
  });
}

/**
 * @description 九宫格抽奖 -- 创建
 */
export function lotteryCreateApi(data) {
  return request({
    url: `marketing/lottery/add`,
    method: 'post',
    data,
  });
}
/**
 **
 * @description 九宫格抽奖 -- 修改/编辑
 */
export function lotteryEditApi(id, data) {
  return request({
    url: `marketing/lottery/edit/${id}`,
    method: 'put',
    data,
  });
}

/**
 **
 * @description 九宫格抽奖 -- 删除
 */
export function lotteryDelApi(id) {
  return request({
    url: `marketing/lottery/del/${id}`,
    method: 'delete',
  });
}

/**
 **
 * @description 九宫格抽奖 -- 显示状态
 */
export function lotteryStatusApi(data) {
  return request({
    url: `marketing/lottery/set_status/${data.id}/${data.status}`,
    method: 'post',
  });
}

/**
 **
 * @description 九宫格抽奖 -- 中奖记录
 */
export function lotteryRecordList(data) {
  return request({
    url: `marketing/lottery/record/list`,
    method: 'get',
    params: data,
  });
}

/**
 **
 * @description 九宫格抽奖 -- 中奖发货/备注处理
 */
export function lotteryRecordDeliver(data) {
  return request({
    url: `marketing/lottery/record/deliver`,
    method: 'post',
    data,
  });
}
