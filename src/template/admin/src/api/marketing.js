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

