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
 * 用户列表导出
 */
export function exportUserList(data) {
  return request({
    url: '/export/user_list',
    method: 'get',
    params: data,
  });
}

/**
 * 订单列表导出
 */
export function exportOrderList(data) {
  return request({
    url: '/export/order_list',
    method: 'get',
    params: data,
  });
}

/**
 * 商品列表导出
 */
export function exportProductList(data) {
  return request({
    url: '/export/product_list',
    method: 'get',
    params: data,
  });
}

/**
 * 砍价列表导出
 */
export function exportBargainList(data) {
  return request({
    url: '/export/bargain_list',
    method: 'get',
    params: data,
  });
}

/**
 * 拼团列表导出
 */
export function exportCombinationList(data) {
  return request({
    url: '/export/combination_list',
    method: 'get',
    params: data,
  });
}

/**
 * 秒杀列表导出
 */
export function exportSeckillList(data) {
  return request({
    url: '/export/seckill_list',
    method: 'get',
    params: data,
  });
}

/**
 * 会员卡导出
 */
export function exportmberCardList(id) {
  return request({
    url: `/export/member_card/${id}`,
    method: 'get',
  });
}