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
 * @description 权限--列表
 * @param {Object} param data {Object} 传值参数
 */
export function getTable (data) {
    return request({
        url: '/setting/menus',
        method: 'get',
        params: data
    })
};

/**
 * 权限 -- 添加
 */
export function addMenus () {
    return request({
        url: '/setting/menus/create',
        method: 'get'
    })
};

/**
 * 权限 -- 编辑
 * @param id
 */
export function editMenus (id) {
  return request({
    url: '/setting/menus/'+id+'/edit',
    method: 'get'
  })
};

/**
 * @description 添加 编辑
 * @param {Object} param data {Object} 集合
 * @param {String} param data.url {String} 地址
 * @param {String} param data.method {String} 请求方式
 * @param {Object} param data.datas {Object} 传值参数
 */
export function addMenusApi (data) {
    return request({
        url: data.url,
        method: data.method,
        data: data.datas
    })
};



/**
 * @description 表单详情
 * @param {Number} param id {Number} 规则id
 */
export function menusDetailsApi (id) {
    return request({
        url: `/setting/menus/${id}`,
        method: 'get'
    })
};

/**
 * @description 修改显示
 * @param {Number} param data.id {Number} 规则id
 * @param {Number} param data.is_show {Number} 状态值
 */
export function isShowApi (data) {
    let datas = {
        is_show: data.is_show
    }
    return request({
        url: `/setting/menus/show/${data.id}`,
        method: 'put',
        data: datas
    })
};

/**
 * @description 权限列表
 */
 export function getRuleList () {
    return request({
        url: `/setting/ruleList`,
        method: 'get'
    });
};
