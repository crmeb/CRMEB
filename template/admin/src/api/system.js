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
 * @description 配置分类--列表
 * @param {Object} param data {Object} 传值参数
 */
export function classListApi (data) {
    return request({
        url: 'setting/config_class',
        method: 'get',
        params: data
    })
}

/**
 * @description 配置分类--新增表单
 * @param {Object} param data {Object} 传值参数
 */
export function classAddApi (data) {
    return request({
        url: 'setting/config_class/create',
        method: 'get'
    })
}

/**
 * @description 配置分类--编辑表单
 * @param {Number} param id {Number} 配置分类id
 */
export function classEditApi (id) {
    return request({
        url: `setting/config_class/${id}/edit`,
        method: 'get'
    })
}

/**
 * @description 配置分类--修改状态
 * @param {Number} param id {Number} 文章id
 */
export function setStatusApi (data) {
    return request({
        url: `setting/config_class/set_status/${data.id}/${data.status}`,
        method: 'PUT'
    })
}

/**
 * @description 配置--列表
 * @param {Object} param data {Object} 传值参数
 */
export function configTabListApi (data) {
    return request({
        url: 'setting/config',
        method: 'get',
        params: data
    })
}

/**
 * @description 配置--新增表单
 * @param {Object} param data {Object} 传值参数
 */
export function configTabAddApi (data) {
    return request({
        url: 'setting/config/create',
        method: 'get',
        params: data
    })
}

/**
 * @description 配置--编辑表单
 * @param {Number} param id {Number} 配置id
 */
export function configTabEditApi (id) {
    return request({
        url: `/setting/config/${id}/edit`,
        method: 'get'
    })
}

/**
 * @description 配置--修改状态
 * @param {Number} param id {Number} 文章id
 */
export function configSetStatusApi (id, status) {
    return request({
        url: `setting/config/set_status/${id}/${status}`,
        method: 'PUT'
    })
}

/**
 * @description 组合数据--列表
 * @param {Object} param data {Object} 传值参数
 */
export function groupListApi (data) {
    return request({
        url: 'setting/group',
        method: 'get',
        params: data
    })
}

/**
 * @description 组合数据--新增
 * @param {Object} param data {Object} 传值参数
 */
export function groupAddApi (data) {
    return request({
        url: data.url,
        method: data.method,
        data: data.datas
    })
}

/**
 * @description 组合数据--详情
 * @param {Number} param id {Number} 组合数据id
 */
export function groupInfoApi (id) {
    return request({
        url: `setting/group/${id}`,
        method: 'get'
    })
}

/**
 * @description 组合数据列表
 * @param {Number} param id {Number} 组合数据id
 */
export function groupDataListApi (id,url) {
    return request({
        url: url,
        method: 'get',
        params: id
    })
}

/**
 * @description 组合数据列表 -- 新增表单
 * @param {Number} param id {Number} 组合数据id
 */
export function groupDataAddApi (id,url) {
    return request({
        url: url,
        method: 'get',
        params: id
    })
}

/**
 * @description 组合数据列表 -- 编辑表单
 * @param {Number} param id {Number} 组合数据列表id
 * @param {Object} param data {Object} 组合数据id对象
 */
export function groupDataEditApi (data,url) {
    return request({
        url: url,
        method: 'get',
        params: data
    })
}

/**
 * @description 组合数据列表 -- 编辑表单
 * @param {Number} param id {Number} 组合数据id
 */
export function groupDataHeaderApi (data,url) {
    return request({
        url: url,
        method: 'get',
        params: data
    })
}

/**
 * @description 组合数据列表 -- 修改状态
 * @param {Object} param data {Object} 组合数据列表传值
 */
export function groupDataSetApi (url) {
    return request({
        url: url,
        method: 'PUT'
    })
}

/**
 * @description 系统日志 -- 搜索条件
 */
export function searchAdminApi (data) {
    return request({
        url: `system/log/search_admin`,
        method: 'GET'
    })
}

/**
 * @description 系统日志 -- 搜索条件
 */
export function systemListApi (params) {
    return request({
        url: `system/log`,
        method: 'GET',
        params
    })
}

/**
 * @description 文件校验 -- 列表
 */
export function fileListApi () {
    return request({
        url: `system/file`,
        method: 'GET'
    })
}

/**
 * @description 数据备份 -- 数据库列表
 */
export function backupListApi () {
    return request({
        url: `system/backup`,
        method: 'GET'
    })
}

/**
 * @description 数据备份 -- 查看表结构详情
 */
export function backupReadListApi (params) {
    return request({
        url: `system/backup/read`,
        method: 'GET',
        params
    })
}

/**
 * @description 数据备份 -- 备份表
 */
export function backupBackupApi (data) {
    return request({
        url: `system/backup/backup`,
        method: 'put',
        data
    })
}

/**
 * @description 数据备份 -- 优化表
 */
export function backupOptimizeApi (data) {
    return request({
        url: `system/backup/optimize`,
        method: 'put',
        data
    })
}

/**
 * @description 数据备份 -- 修复表
 */
export function backupRepairApi (data) {
    return request({
        url: `system/backup/repair`,
        method: 'put',
        data
    })
}

/**
 * @description 数据备份 -- 备份记录表
 */
export function filesListApi (data) {
    return request({
        url: `system/backup/file_list`,
        method: 'GET'
    })
}

/**
 * @description 数据备份 -- 下载备份记录表
 */
export function filesDownloadApi (params) {
    return request({
        url: `backup/download`,
        method: 'get',
        params
    })
}

/**
 * @description 数据备份 -- 导入
 */
export function filesImportApi (data) {
    return request({
        url: `system/backup/import`,
        method: 'POST',
        data
    })
}

/**
 * @description 文件管理 -- 列表
 */
export function opendirListApi (params) {
    return request({
        url: `system/file/opendir`,
        method: 'GET',
        params
    })
}

/**
 * @description 文件管理 -- 读取文件
 */
export function openfileApi (filepath) {
    return request({
        url: `system/file/openfile?filepath=${filepath}`,
        method: 'GET'
    })
}

/**
 * @description 文件管理 -- 保存
 */
export function savefileApi (data) {
    return request({
        url: `system/file/savefile`,
        method: 'post',
        data
    })
}

/**
 * @description 安全维护 -- 更换域名
 */
export function replaceSiteUrlApi (data) {
    return request({
        url: `system/replace_site_url`,
        method: 'post',
        data
    })
}

/**
 *
 */
export function auth () {
    return request({
        url: 'auth',
        method: 'get'
    })
}

/**
 * @description 申请授权
 * @param data
 */
export function authApply (data) {
    return request({
        url: 'auth_apply',
        method: 'post',
        data
    })
}

/**
 * @description 获取客服页面广告
 * @param data
 */
export function getKfAdv () {
    return request({
        url: 'setting/get_kf_adv',
        method: 'get',
    })
}

/**
 * @description 设置客服页面广告
 * @param data
 */
export function setKfAdv (data) {
    return request({
        url: 'setting/set_kf_adv',
        method: 'post',
        data
    })
}

/**
 * @description 数据配置
 * @param data
 */
export function groupAllApi () {
    return request({
        url: 'setting/group_all',
        method: 'get'
    })
}
/**
 * APP版本列表
 */
 export function versionList (params) {
    return request({
        url: `system/version_list`,
        method: 'get',
        params
    })
}
/**
 * APP版本列表
 */
 export function versionCrate () {
    return request({
        url: `system/version_crate`,
        method: 'get'
    })
}

/**
 * @description 数据配置保存
 */
 export function groupSaveApi(data) {
    return request({
        url: `setting/group_data/save_all`,
        method: 'POST',
        data
    });
}

/**
 * @description 获取pc商城端logo
 */
export function pcLogoApi(id) {
    return request({
        url: `setting/config/get_system/${id}`,
        method: 'get'
    });
}


/**
 * @description pc商城端logo
 */
export function pcLogoSave(data) {
    return request({
        url: `setting/config/save_basics`,
        method: 'POST',
        data
    });
}
/**
 * @description 获取隐私协议
 * @param data
 */
 export function getAgreement() {
    return request({
        url: 'setting/get_user_agreement',
        method: 'get',
    })
}


/**
 * @description 设置隐私协议
 * @param data
 */
export function setAgreement(data) {
    return request({
        url: 'setting/set_user_agreement',
        method: 'post',
        data
    })
}