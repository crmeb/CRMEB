// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2021 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

import request from '@/libs/request'

/**
 * @description 优惠券制作--列表
 * @param {Object} param params {Object} 传值参数
 */
export function couponListApi (params) {
    return request({
        url: 'marketing/coupon/list',
        method: 'get',
        params
    })
}

/**
 * @description 优惠券制作--新增表单
 * type:添加优惠券类型0：通用，1：品类，2：商品
 */
export function couponCreateApi (type) {
    return request({
        url: `marketing/coupon/create/${type}`,
        method: 'get'
    })
}

/**
 * @description 优惠券制作--编辑表单
 */
export function couponEditeApi (id) {
    return request({
        url: `marketing/coupon/${id}/edit`,
        method: 'get'
    })
}

/**
 * @description 优惠券制作--发布优惠券表单
 * @param {Number} param id {Number} 优惠券id
 */
export function couponSendApi (id) {
    return request({
        url: `marketing/coupon/issue/${id}`,
        method: 'get'
    })
}

/**
 * @description 已发布管理--列表
 * @param {Object} param params {Object} 传值参数
 */
export function releasedListApi (params) {
    return request({
        url: 'marketing/coupon/released',
        method: 'get',
        params
    })
}

/**
 * @description 已发布管理--领取记录
 * @param {Number} param id {Number} 已发布优惠券id
 */
export function releasedissueLogApi (id) {
    return request({
        url: `marketing/coupon/released/issue_log/${id}`,
        method: 'get'
    })
}

/**
 * @description 已发布管理--修改状态表单
 * @param {Number} param id {Number} 已发布优惠券id
 */
export function releaseStatusApi (id) {
    return request({
        url: `marketing/coupon/released/${id}/status`,
        method: 'get'
    })
}

/**
 * @description 优惠券列表--是否开启
 * @param {*} data
 */
export function couponStatusApi (data) {
    return request({
        url: `marketing/coupon/status/${data.id}/${data.status}`,
        method: 'get'
    });
}

/**
 * @description 优惠券制作--品类
 * @param {*} type 默认 1
 */
export function couponCategoryApi (type) {
    return request({
        url: `product/category/tree/${type}`,
        method: 'get'
    });
}

/**
 * @description 优惠券制作--保存
 */
export function couponSaveApi (data) {
    return request({
        url: `marketing/coupon/save_coupon`,
        method: 'post',
        data
    });
}

/**
 * @description 优惠券
 * @param {*} id
 */
export function couponDetailApi (id) {
    return request({
        url: `marketing/coupon/copy/${id}`,
        method: 'get'
    });
}

/**
 * @description 会员领取记录 -- 列表
 * @param {Object} param params {Object} 传值参数
 */
export function userListApi (params) {
    return request({
        url: `/marketing/coupon/user`,
        method: 'get',
        params
    })
}

/**
 * @description 砍价商品 -- 列表
 * @param {Object} param params {Object} 传值参数
 */
export function bargainListApi (params) {
    return request({
        url: `marketing/bargain`,
        method: 'get',
        params
    })
}

/**
 * @description 砍价商品 -- 详情
 * @param {Number} param id {Number} 砍价商品id
 */
export function bargainInfoApi (id) {
    return request({
        url: `marketing/bargain/${id}`,
        method: 'get'
    })
}

/**
 * @description 砍价商品 -- 保存编辑
 * @param {Object} param data {Object} 传值参数
 */
export function bargainCreatApi (data) {
    return request({
        url: `marketing/bargain/${data.id}`,
        method: 'POST',
        data
    })
}

/**
 * @description 砍价商品 -- 修改状态
 * @param {Object} param data {Object} 传值参数
 */
export function bargainSetStatusApi (data) {
    return request({
        url: `marketing/bargain/set_status/${data.id}/${data.status}`,
        method: 'PUT'
    })
}

/**
 * @description 拼团商品 -- 列表
 * @param {Object} param data {Object} 传值参数
 */
export function combinationListApi (params) {
    return request({
        url: `marketing/combination`,
        method: 'get',
        params
    })
}

/**
 * @description 拼团商品 -- 修改状态
 * @param {Object} param data {Object} 传值参数
 */
export function combinationSetStatusApi (data) {
    return request({
        url: `marketing/combination/set_status/${data.id}/${data.status}`,
        method: 'PUT'
    })
}

/**
 * @description 拼团商品 -- 拼团统计
 * @param {Object} param data {Object} 传值参数
 */
export function statisticsApi () {
    return request({
        url: `marketing/combination/statistics`,
        method: 'GET'
    })
}

/**
 * @description 拼团商品 -- 详情
 * @param {Number} param id {Number} 拼团商品id
 */
export function combinationInfoApi (id) {
    return request({
        url: `marketing/combination/${id}`,
        method: 'get'
    })
}

/**
 * @description 拼团商品 -- 保存编辑
 * @param {Object} param data {Object} 传值参数
 */
export function combinationCreatApi (data) {
    return request({
        url: `marketing/combination/${data.id}`,
        method: 'POST',
        data
    })
}

/**
 * @description 拼团商品 -- 拼团列表
 */
export function combineListApi (params) {
    return request({
        url: `marketing/combination/combine/list`,
        method: 'GET',
        params
    })
}

/**
 * @description 拼团商品 -- 拼团人列表
 * @param {Number} param id {Number} 拼团商品id
 */
export function orderPinkListApi (id) {
    return request({
        url: `marketing/combination/order_pink/${id}`,
        method: 'GET'
    })
}

/**
 * @description 秒杀商品 -- 列表
 */
export function seckillListApi (params) {
    return request({
        url: `marketing/seckill`,
        method: 'GET',
        params
    })
}

/**
 * @description 秒杀商品 -- 详情
 */
export function seckillInfoApi (id) {
    return request({
        url: `marketing/seckill/${id}`,
        method: 'GET'
    })
}

/**
 * @description 秒杀商品 -- 保存编辑
 */
export function seckillAddApi (data) {
    return request({
        url: `marketing/seckill/${data.id}`,
        method: 'post',
        data
    })
}

/**
 * @description 秒杀商品 -- 修改状态
 */
export function seckillStatusApi (data) {
    return request({
        url: `marketing/seckill/set_status/${data.id}/${data.status}`,
        method: 'put'
    })
}

/**
 * @description 积分日志 -- 列表
 */
export function integralListApi (params) {
    return request({
        url: `marketing/integral`,
        method: 'GET',
        params
    })
}

/**
 * @description 积分日志 -- 头部
 */
export function integralStatisticsApi (params) {
    return request({
        url: `marketing/integral/statistics`,
        method: 'GET',
        params
    })
}

/**
 * @description 积分日志 -- 头部
 */
export function seckillTimeListApi () {
    return request({
        url: `marketing/seckill/time_list`,
        method: 'GET'
    })
}

/**
 * @description 积分日志 -- 头部
 */
export function productAttrsApi (id, type) {
    return request({
        url: `product/product/attrs/${id}/${type}`,
        method: 'GET'
    })
}

/**
 * @description 砍价商品 -- 列表
 * @param {Object} param params {Object} 传值参数
 */
export function bargainUserListApi (params) {
    return request({
        url: `marketing/bargain_list`,
        method: 'get',
        params
    });
}

/**
 * @description 砍价商品 -- 列表
 * @param {Object} param params {Object} 传值参数
 */
export function bargainUserInfoApi (id) {
    return request({
        url: `marketing/bargain_list_info/${id}`,
        method: 'get'
    });
}

/**
 * @description 已发布管理 -- 删除
 */
export function delCouponReleased (id) {
    return request({
        url: `marketing/coupon/released/${id}`,
        method: 'DELETE'
    })
}

/**
 * @description 积分日志 -- 导出
 */
export function userPointApi (data) {
    return request({
        url: `export/userPoint`,
        method: 'get',
        params: data
    })
}

/**
 * @description 商铺砍价活动 -- 导出
 */
export function stroeBargainApi (data) {
    return request({
        url: `export/storeBargain`,
        method: 'get',
        params: data
    })
}

/**
 * @description 商铺拼团 -- 导出
 */
export function storeCombinationApi (data) {
    return request({
        url: `export/storeCombination`,
        method: 'get',
        params: data
    })
}

/**
 * @description 商铺秒杀 -- 导出
 */
export function storeSeckillApi (data) {
    return request({
        url: `export/storeSeckill`,
        method: 'get',
        params: data
    })
}
