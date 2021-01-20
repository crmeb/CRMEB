import request from '@/libs/request'

/**
 * @description 附件分类--列表
 * @param {Object} param data {Object} 传值参数
 */
export function getCategoryListApi (data) {
    return request({
        url: 'file/category',
        method: 'get',
        params: data
    })
}

/**
 * @description 添加分类
 */
export function createApi (id) {
    return request({
        url: 'file/category/create',
        method: 'get',
        params: id
    })
}

/**
 * @description 编辑分类
 * @param {Number} param id {Number} 分类id
 */
export function categoryEditApi (id) {
    return request({
        url: `file/category/${id}/edit`,
        method: 'get'
    })
}

/**
 * @description 删除分类
 * @param {Number} param id {Number} 分类id
 */
export function categoryDelApi (id) {
    return request({
        url: `file/category/${id}`,
        method: 'DELETE'
    })
}

/**
 * @description 附件列表
 * @param {Object} param data {Object} 传值
 */
export function fileListApi (data) {
    return request({
        url: 'file/file',
        method: 'get',
        params: data
    })
}

/**
 * @description 移动分类，修改附件分类表单
 * @param {Object} param data {Object} 传值
 */
export function moveApi (data) {
    return request({
        url: 'file/file/do_move',
        method: 'put',
        data
    })
}

/**
 * @description 修改附件名称
 * @param {String} param ids {String} 图片id拼接成的字符串
 */
export function fileUpdateApi (ids,data) {
    return request({
        url: 'file/file/update/'+ids,
        method: 'put',
        data
    });
}

/**
 * @description 删除附件
 * @param {String} param ids {String} 图片id拼接成的字符串
 */
export function fileDelApi (ids) {
    return request({
        url: 'file/file/delete',
        method: 'post',
        data: ids
    })
}
