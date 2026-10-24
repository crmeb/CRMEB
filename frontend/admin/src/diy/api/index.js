// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

import request from '@/libs/request';

/**
 * @description 获取分类
 */
export function categoryList() {
  return request({
    url: '/cms/category_list',
    method: 'get',
  });
}

/**
 * @description 恢复模板初始数据
 * @param {Object} param data {Object} 传值参数
 */
export function recovery(id) {
  return request({
    url: 'diy/recovery/' + id,
    method: 'get',
  });
}

/**
 * @description 设置初始数据
 * @param {Object} param data {Object} 传值参数
 */
export function setDefault(id) {
  return request({
    url: 'diy/set_recovery/' + id,
    method: 'get',
  });
}

/**
 * @description 保存DIY数据
 * @param {Object} param data {Object} 传值参数
 */
export function diySave(id, data) {
  return request({
    url: 'diy/save/' + id,
    method: 'post',
    data: data,
  });
}

/**
 * @description 保存DIY数据
 * @param {Object} param data {Object} 传值参数
 */
export function saveDiy(id, data) {
  return request({
    url: 'diy/diy_save/' + id,
    method: 'post',
    data: data,
  });
}

/**
 * @description 获取可视化数据
 * @param {Object} param data {Object} 传值参数
 */
export function diyGetInfo(id, data) {
  return request({
    url: 'diy/get_info/' + id,
    method: 'get',
    params: data,
  });
}

/**
 * @description 使用diy模板(活动商品)
 * @param {Object} param data {Object} 传值参数
 */
export function getGroomList(type, data) {
  return request({
    url: 'diy/groom_list/' + type,
    method: 'get',
    params: data,
  });
}

/**
 * @description 获取商品列表
 */
export function getProduct(data) {
  return request({
    url: 'diy/get_product',
    method: 'get',
    params: data,
  });
}

/**
 * @description 获取自定义组件字段
 */
export function getDiyField() {
  return request({
    url: 'diy_pro/text/field',
    method: 'get',
  });
}

/**
 * @description 获取 DIY 组件可用状态（当前可用的组件白名单，后端判定）
 * DIY 编辑器对插件归属组件（pluginComponent 标记）按白名单放行：插件代码被裁剪、
 * 未安装或未启用时组件不在名单内，面板隐藏、画布已存在节点渲染占位
 */
export function getDiyComponentStatus() {
  return request({
    url: 'diy/component_status',
    method: 'get',
  });
}

/**
 * @description 获取DIY数据
 * @param {Object} param data {Object} 传值参数
 */
export function getDiyInfo(id) {
  return request({
    url: 'diy/get_diy_info/' + id,
    method: 'get',
  });
}

/**
 * @description 获取链接列表
 */
export function getUrl() {
  return request({
    url: 'diy/get_url',
    method: 'get',
  });
}

/**
 * @description 获取产品分类
 */
export function getCategory() {
  return request({
    url: 'diy/get_category',
    method: 'get',
  });
}

/**
 * @description 获取产品一或二级分类
 */
export function getByCategory(data) {
  return request({
    url: 'diy/get_by_category',
    method: 'get',
    params: data,
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
    params: data,
  });
}

/**
 * @description 删除DIY数据
 * @param {Object} param data {Object} 传值参数
 */
export function diyDel(id) {
  return request({
    url: 'diy/del/' + id,
    method: 'delete',
  });
}

/**
 * @description 使用diy模板
 * @param {Object} param data {Object} 传值参数
 */
export function setStatus(id) {
  return request({
    url: 'diy/set_status/' + id,
    method: 'put',
  });
}

/**
 * @description 使用diy模板(判断是否显示周边门店列表)
 * @param {Object} param data {Object} 传值参数
 */
export function storeStatus() {
  return request({
    url: 'diy/get_store_status',
    method: 'get',
  });
}

/**
 * @description 添加模板
 * @param {Object} param data {Object} 传值参数
 */
export function getDiyCreate() {
  return request({
    url: 'diy/create',
    method: 'get',
  });
}

/**
 * @description 设置默认数据
 * @param {Object} param data {Object} 传值参数
 */
export function getRecovery(id) {
  return request({
    url: 'diy/set_recovery/' + id,
    method: 'get',
  });
}

/**
 * @description 手动添加,弹窗列表数据
 * @param {Object} param data {Object} 传值参数
 */
export function getProductList(params) {
  return request({
    url: 'diy/get_product_list',
    method: 'get',
    params,
  });
}

/**
 * @description 换色 -- 一键换色、分类提交；
 */
export function colorChange(status, name) {
  return request({
    url: `diy/color_change/${status}/${name}`,
    method: 'put',
  });
}

/**
 * @description 换色 -- 一键换色、分类信息；
 */
export function getColorChange(name) {
  return request({
    url: `diy/get_color_change/${name}`,
    method: 'get',
  });
}

/**
 * @description 个人中心-获取信息；
 */
export function getMember() {
  return request({
    url: `diy/get_member`,
    method: 'get',
  });
}

/**
 * @description 小程序 -- 二维码；
 */
export function getRoutineCode(id) {
  return request({
    url: `diy/get_routine_code/${id}`,
    method: 'get',
  });
}

/**
 * @description 主题预览 -- 小程序二维码（theme/routine_code/:id，page_type=micro 时指向微页面预览页）；
 */
export function getThemeRoutineCode(id, pageType) {
  return request({
    url: `theme/routine_code/${id}`,
    method: 'get',
    params: pageType ? { page_type: pageType } : {},
  });
}

/**
 * @description 个人中心-提交信息；
 */
export function memberSave(data) {
  return request({
    url: `diy/member_save`,
    method: 'post',
    data: data,
  });
}

/**
 * @description 页面链接-获取分类；
 */
export function pageCategory() {
  return request({
    url: `diy/get_page_category`,
    method: 'get',
  });
}

/**
 * @description 页面链接-获取链接；
 */
export function pageLink(id) {
  return request({
    url: `diy/get_page_link/${id}`,
    method: 'get',
  });
}

/**
 * @description 页面链接-自定义链接提交；
 */
export function saveLink(data, id) {
  return request({
    url: `diy/save_link/${id}`,
    method: 'post',
    data: data,
  });
}

/**
 * @description diy页面-热搜词；
 */
export function getWordsAll() {
  return request({
    url: `product/words/get_all`,
    method: 'get',
  });
}
/**
 * @description diy模板导出
 */
export function exportDiyDataApi(id) {
  return request({
    url: `diy_pro/export/data/${id}`,
    method: 'get',
  });
}

/**
 * @description 保存DIY名称
 * @param {Object} param data {Object} 传值参数
 */
export function diyUpdateName(id, data) {
  return request({
    url: 'diy_pro/update/name/' + id,
    method: 'post',
    data: data,
  });
}

/** 5.6+版本使用 */

/**
 * @description DIY模板列表
 * @param {Object} param data {Object} 传值参数
 */
export function diyProList(data) {
  return request({
    url: 'diy_pro/get_list',
    method: 'get',
    params: data,
  });
}

/**
 * @description 获取可视化数据
 * @param {Object} param data {Object} 传值参数
 */
export function diyProInfo(id, data) {
  return request({
    url: 'diy_pro/get_info/' + id,
    method: 'get',
    params: data,
  });
}

/**
 * @description 保存DIY数据
 * @param {Object} param data {Object} 传值参数
 */
export function diyProSave(id, data) {
  return request({
    url: 'diy_pro/save/' + id,
    method: 'post',
    data: data,
  });
}
/**
 * @description 保存商城主题
 * @param type 保存类型
 * @param value diy格值
 */
export function themeSave(id, data) {
  return request({
    url: 'theme/save/' + id,
    method: 'post',
    data: data,
  });
}
/**
 * @description 获取商城主题
 * @param id 主题id
 * @param type 类型
 * @return {Object} 主题数据
 */
export function themeInfo(id, type) {
  return request({
    url: 'theme/info/' + id + '/' + type,
    method: 'get',
  });
}

/**
 * @description 获取文章列表
 */
export function getArticleList(data) {
  return request({
    url: 'theme/article',
    method: 'get',
    params: data,
  });
}

/**
 * @description 获取优惠券列表
 */
export function getCouponList(data) {
  return request({
    url: 'theme/coupon',
    method: 'get',
    params: data,
  });
}

/**
 * @description 获取商品列表
 */
export function getProProduct(data) {
  return request({
    url: 'diy_pro/get_product',
    method: 'get',
    params: data,
  });
}

/**
 * @description 获取主题商品列表
 */
export function getThemeProduct(data) {
  return request({
    url: 'theme/product',
    method: 'get',
    params: data,
  });
}

/**
 * @description 导入主题
 * @param data
 */
export function importTheme(data) {
  return request({
    url: 'theme/import',
    method: 'post',
    data,
  });
}
/**
 * @description 保存主题名称
 * @param id 主题id
 * @param data 主题名称
 */
export function saveThemeTitle(id, data) {
  return request({
    url: 'theme/save_title/' + id,
    method: 'post',
    data: data,
  });
}

/**
 * @description 保存主题封面
 * @param id 主题id
 * @param data 封面图地址
 */
export function saveThemeImage(id, data) {
  return request({
    url: 'theme/save_image/' + id,
    method: 'post',
    data: data,
  });
}

/**
 * @description 获取主题列表
 */
export function getThemeList(data) {
  return request({
    url: 'theme/list',
    method: 'get',
    params: data,
  });
}
/**
 * @description 导出主题
 * @param id 主题id
 */
export function exportTheme(id) {
  return request({
    url: 'theme/export/' + id,
    method: 'get',
  });
}

/**
 * @description 查询主题导出记录（轮询用）
 * @param recordId 下载记录id
 */
export function getExportRecord(recordId) {
  return request({
    url: 'theme/export_record/' + recordId,
    method: 'get',
  });
}

/**
 * @description 使用主题
 * @param id 主题id
 */
export function useTheme(id) {
  return request({
    url: 'theme/use/' + id,
    method: 'get',
  });
}

/**
 * @description 获取当前使用的主题
 */
export function getThemeUsing() {
  return request({
    url: 'theme/using',
    method: 'get',
  });
}
/**
 * @description 恢复主题
 * @param id 主题id
 */
export function restoreTheme(id) {
  return request({
    url: 'theme/restore/' + id,
    method: 'get',
  });
}

/**
 * @description 使用主题数据
 * @param id 当前主题id
 * @param data {theme_id, type}
 */
export function useThemeData(id, data) {
  return request({
    url: 'theme/use_data/' + id,
    method: 'get',
    params: data,
  });
}

/**
 * @description 删除主题
 * @param id 主题id
 */
export function deleteTheme(id) {
  return request({
    url: 'theme/del/' + id,
    method: 'delete',
  });
}

/**
 * @description 获取微页面列表
 * @param data
 */
export function getMicroPageList(data) {
  return request({
    url: 'theme/micro_page',
    method: 'get',
    params: data,
  });
}
