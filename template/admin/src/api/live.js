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
 * @description 直播列表
 */
export function liveList(params) {
  return request({
    url: 'live/room/list',
    method: 'get',
    params,
  });
}

/**
 * @description 直播列表
 */
export function liveAdd(data) {
  return request({
    url: 'live/room/add',
    method: 'post',
    data,
  });
}

/**
 * @description 直播列表详情
 */
export function liveDetail(id) {
  return request({
    url: 'live/room/detail/' + id,
    method: 'get',
  });
}

/**
 * @description 直播间设置是否显示
 */
export function liveShow(id, type) {
  return request({
    url: `live/room/set_show/${id}/${type}`,
    method: 'get',
  });
}

/**
 * @description 直播商品列表
 */
export function liveGoods(params) {
  return request({
    url: 'live/goods/list',
    method: 'get',
    params,
  });
}

/**
 * @description 直播商品列表生成直播商品
 */
export function liveGoodsCreat(data) {
  return request({
    url: 'live/goods/create',
    method: 'post',
    data,
  });
}

/**
 * @description 直播商品列表添加
 */
export function liveGoodsAdd(data) {
  return request({
    url: 'live/goods/add',
    method: 'post',
    data,
  });
}

/**
 * @description 直播间添加商品
 */
export function liveRoomGoodsAdd(data) {
  return request({
    url: 'live/room/add_goods',
    method: 'post',
    data,
  });
}

/**
 * @description 同步直播间
 */
export function liveSyncRoom() {
  return request({
    url: 'live/room/syncRoom',
    method: 'get',
  });
}

/**
 * @description 同步商品
 */
export function liveSyncGoods() {
  return request({
    url: 'live/goods/syncGoods',
    method: 'get',
  });
}

/**
 * @description 主播列表
 */
export function liveAuchorList(params) {
  return request({
    url: 'live/anchor/list',
    method: 'get',
    params,
  });
}

/**
 * @description 主播添加/修改获取表单
 */
export function liveAuchorAdd(id) {
  return request({
    url: 'live/anchor/add/' + id,
    method: 'get',
  });
}

/**
 * @description 直播商品详情
 */
export function liveGoodsDetail(id) {
  return request({
    url: 'live/goods/detail/' + id,
    method: 'get',
  });
}

/**
 * @description 直播商品显示
 */
export function liveGoodsShow(id, type) {
  return request({
    url: `live/goods/set_show/${id}/${type}`,
    method: 'get',
  });
}
