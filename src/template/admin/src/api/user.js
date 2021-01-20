import request from '@/libs/request'

/**
 * @description 用户管理--列表
 * @param {Object} param data {Object} 传值参数
 */
export function userList (data) {
    return request({
        url: 'user/user',
        method: 'get',
        params: data
    })
}

/**
 * @description 编辑表单数据
 * @param {Number} param id {Number} 会员id
 */
export function getUserData (id) {
    return request({
        url: `user/user/${id}/edit`,
        method: 'get'
    })
}



/**
 * @description 会员管理修改显示
 * @param {Object} param data {Object} 传入的状态值，用户id
 */
export function isShowApi (data) {
    return request({
        url: `user/set_status/${data.status}/${data.id}`,
        method: 'put'
    })
}

/**
 * @description 优惠券列表
 * @param {Object} param params {Object} 传值
 */
export function couponApi (params) {
    return request({
        url: `marketing/coupon/grant`,
        method: 'get',
        params
    })
}

/**
 * @description 发送优惠券
 * @param {Object} param data {Object} 传值
 */
export function sendCouponApi (data) {
    return request({
        url: `marketing/coupon/user/grant`,
        method: 'POST',
        data
    })
}

/**
 * @description 修改积分余额表单
 * @param {Number} param id {Number} 用户id
 */
export function editOtherApi (id) {
    return request({
        url: `user/edit_other/${id}`,
        method: 'get'
    })
}

/**
 * @description 会员管理-详情
 * @param {Number} param id {Number} 用户id
 */
export function detailsApi (id) {
    return request({
        url: `user/user/${id}`,
        method: 'get'
    })
}

/**
 * @description 会员管理详情中tab选项
 * @param {Number} param id {Number} 用户id
 */
export function infoApi (data) {
    return request({
        url: `user/one_info/${data.id}`,
        method: 'get',
        params: data.datas
    })
}



/**
 * @description 会员分组-列表
 * @param {Object} param data {Object} 传值参数
 */
export function userGroupApi (data) {
    return request({
        url: 'user/user_group/list',
        method: 'get',
        params: data
    })
}

/**
 * @description 会员删除 --- 删除分组
 * @param {Number} param id {Number} 会员id
 */
export function groupDelApi (id) {
    return request({
        url: `user/user_group/del/${id}`,
        method: 'DELETE'
    })
}

/**
 * @description 会员添加表单/删除表单 --- 表单
 * @param {Number} param id {Number} 会员id
 */
export function groupAddApi (id) {
    return request({
        url: `user/user_group/add/${id}`,
        method: 'get'
    })
}

/**
 * @description 个人中心 --- 修改密码
 * data 请求参数
 */
export function updtaeAdmin (data) {
    return request({
        url: `setting/update_admin`,
        method: 'PUT',
        data
    })
}

/**
 * @description 个人中心 --- 设置会员等级
 * data 请求参数
 */
export function userSetGroup (data) {
    return request({
        url: `user/set_group`,
        method: 'post',
        data
    })
}

/**
 * @description 个人中心 --- 会员标签列表
 * data 请求参数
 */
export function userLabelApi (data) {
    return request({
        url: `user/user_label`,
        method: 'get',
        params: data
    })
}

/**
 * @description 获取标签分类（全部）
 * data 请求参数
 */
export function userLabelAll (data) {
    return request({
        url: `user/user_label_cate/all`,
        method: 'get',
        params: data
    });
}

/**
 * 添加用户
 */
export function getUserSaveForm () {
    return request({
        url: `/user/user/create`,
        method: 'get'
    });
}

/**
 * 同步用户
 */
export function userSynchro () {
    return request({
        url: `/user/user/syncUsers`,
        method: 'get'
    });
}

/**
 * @description 获取用户标签分类编辑表单
 * data 请求参数
 */
export function userLabelEdit (id) {
    return request({
        url: `user/user_label_cate/${id}/edit`,
        method: 'get'
    });
}

/**
 * @description 获取用户标签分类创建表单
 * data 请求参数
 */
export function userLabelCreate (id) {
    return request({
        url: `user/user_label_cate/create`,
        method: 'get'
    });
}

/**
 * @description 个人中心 --- 会员标签表单生成
 * data 请求参数
 */
export function userLabelAddApi (id) {
    return request({
        url: `user/user_label/add/${id}`,
        method: 'get'
    })
}

/**
 * @description 个人中心 --- 获取设置会员标签表单
 * data 请求参数
 */
export function userSetLabelApi (data) {
    return request({
        url: `user/set_label`,
        method: 'post',
        data
    })
}


/**
 * 获取用户标签
 */
export function getUserLabel (uid) {
    return request({
        url: `user/label/${uid}`,
        method: 'get'
    });
}

/**
 * 设置用户标签
 */
export function putUserLabel (uid,data) {
    return request({
        url: `user/label/${uid}`,
        method: 'post',
        data
    });
}
