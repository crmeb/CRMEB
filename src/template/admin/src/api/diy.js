import request from '@/libs/request'

/**
 * @description 保存DIY数据
 * @param {Object} param data {Object} 传值参数
 */
export function diySave(id, data) {
    return request({
        url: 'diy/save/' + id,
        method: 'post',
        data: data
    });
}

/**
 * @description 获取DIY数据
 * @param {Object} param data {Object} 传值参数
 */
export function diyGetInfo(id, data) {
    return request({
        url: 'diy/get_info/' + id,
        method: 'get',
        params: data
    });
}

/**
 * @description 获取链接列表
 */
export function getUrl() {
    return request({
        url: 'diy/get_url',
        method: 'get'
    });
}

/**
 * @description 获取产品分类
 */
export function getCategory() {
    return request({
        url: 'diy/get_category',
        method: 'get'
    });
}

/**
 * @description 获取产品一或二级分类
 */
export function getByCategory(data) {
    return request({
        url: 'diy/get_by_category',
        method: 'get',
        params: data
    });
}

/**
 * @description DIY模板列表
 * @param {Object} param data {Object} 传值参数
 */
export function diyList(data) {
    return request({
        url: 'diy/get_list',
        method: 'get',
        params: data
    });
}

/**
 * @description 删除DIY数据
 * @param {Object} param data {Object} 传值参数
 */
export function diyDel(id) {
    return request({
        url: 'diy/del/' + id,
        method: 'delete'
    });
}

/**
 * @description 使用diy模板
 * @param {Object} param data {Object} 传值参数
 */
export function setStatus(id) {
    return request({
        url: 'diy/set_status/' + id,
        method: 'put'
    });
}

/**
 * @description 恢复模板初始数据
 * @param {Object} param data {Object} 传值参数
 */
export function recovery(id) {
    return request({
        url: 'diy/recovery/' + id,
        method: 'get'
    });
}
