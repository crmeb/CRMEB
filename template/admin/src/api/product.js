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

/*
 * 获取商品表单头数量；
 * */
export function getGoodHeade() {
  return request({
    url: 'product/product/type_header',
    method: 'get',
  });
}

/*
 * 获取商品表单头数量；
 * */
export function getGoodsCategory(data) {
  return request({
    url: '/goods/goods_category',
    method: 'get',
    params: data,
  });
}

/**
 * @description 商品管理-- 列表
 */
export function getGoods(params) {
  return request({
    url: 'product/product',
    method: 'get',
    params,
  });
}

/**
 * @description 商品管理-- 临时保存
 */
export function productCache() {
  return request({
    url: 'product/cache',
    method: 'get',
  });
}

/**
 * @description 商品管理-- 取消临时保存
 */
export function cacheDelete() {
  return request({
    url: 'product/cache',
    method: 'delete',
  });
}

/**
 * @description 商品管理-- 上下架
 */
export function PostgoodsIsShow(id, isShow) {
  return request({
    url: `product/product/set_show/${id}/${isShow}`,
    method: 'put',
  });
}

/**
 * @description 商品属性 -- 批量上下架
 * @param {Object} param data {Object} 传值对象
 */
export function productShowApi(data) {
  return request({
    url: `product/product/product_show`,
    method: 'put',
    data,
  });
}

/**
 * @description 商品属性 -- 批量下架
 * @param {Object} param data {Object} 传值对象
 */
export function productUnshowApi(data) {
  return request({
    url: `product/product/product_unshow`,
    method: 'put',
    data,
  });
}

/**
 * @description 商品管理-- 分类
 */
export function treeListApi(type) {
  return request({
    url: `product/category/tree/${type}`,
    method: 'get',
  });
}

/**
 * @description 商品管理-- 详情
 */
export function productInfoApi(id) {
  return request({
    url: `product/product/${id}`,
    method: 'get',
  });
}

/**
 * @description 商品管理-- 提交
 */
export function productAddApi(data) {
  return request({
    url: `product/product/${data.id}`,
    method: 'POST',
    data,
  });
}

/**
 * @description 商品分类 -- 列表
 * @param {Object} param params {Object} 传值参数
 */
export function productListApi(params) {
  return request({
    url: 'product/category',
    method: 'get',
    params,
  });
}

/**
 * @description 商品分类 -- 添加表单
 * @param {Object} param params {Object} 传值参数
 */
export function productCreateApi() {
  return request({
    url: 'product/category/create',
    method: 'get',
  });
}

/**
 * @description 商品分类 -- 编辑表单
 * @param {Object} param params {Object} 传值参数
 */
export function productEditApi(id) {
  return request({
    url: `product/category/${id}`,
    method: 'get',
  });
}

/**
 * @description 商品分类 -- 修改状态
 * @param {Object} param params {Object} 传值参数
 */
export function setShowApi(data) {
  return request({
    url: `product/category/set_show/${data.id}/${data.is_show}`,
    method: 'PUT',
  });
}

/**
 * @description 选择商品 -- 列表
 */
export function changeListApi(params) {
  return request({
    url: `product/product/list`,
    method: 'GET',
    params,
  });
}

/**
 * @description 商品评论 -- 列表
 * @param {Object} param params {Object} 传值参数
 */
export function replyListApi(params) {
  return request({
    url: `product/reply`,
    method: 'get',
    params,
  });
}

/**
 * @description 商品评论 -- 回复
 * @param {Object} param data {Object} 传值参数
 */
export function setReplyApi(data, id) {
  return request({
    url: `product/reply/set_reply/${id}`,
    method: 'PUT',
    data,
  });
}

/**
 * @description 获取复制商品配置
 */
export function copyConfigApi() {
  return request({
    url: `product/copy_config`,
    method: 'get',
  });
}

/**
 * @description 商品管理 -- 获取京东、淘宝商品数据
 * @param {Object} param data {Object} 传值参数
 */
export function crawlFromApi(data) {
  return request({
    url: `product/copy`,
    method: 'POST',
    data,
  });
}

/**
 * @description 商品管理 -- 京东、淘宝商品数据提交
 * @param {Object} param data {Object} 传值参数
 */
export function crawlSaveApi(data) {
  return request({
    url: `product/crawl/save`,
    method: 'POST',
    data,
  });
}

/**
 * @description 商品管理 -- 生成属性
 * @param {Object} param data {Object} 传值参数
 */
export function generateAttrApi(data, id, type) {
  return request({
    url: `product/generate_attr/${id}/${type}`,
    method: 'POST',
    data,
  });
}

/**
 * @description 商品属性 -- 列表
 * @param {Object} param params {Object} 传值参数
 */
export function ruleListApi(params) {
  return request({
    url: `product/product/rule`,
    method: 'GET',
    params,
  });
}

/**
 * @description 商品属性 -- 添加
 * @param {Number} param id {Number} 属性id
 * @param {Object} param data {Object} 传值参数
 */
export function ruleAddApi(data, id) {
  return request({
    url: `product/product/rule/${id}`,
    method: 'POST',
    data,
  });
}

/**
 * @description 商品属性 -- 详情
 * @param {Number} param id {Number} 属性id
 */
export function ruleInfoApi(id) {
  return request({
    url: `product/product/rule/${id}`,
    method: 'get',
  });
}

/**
 * @description 商品评价 -- 虚拟评价
 * @id--产品id；
 */
export function fictitiousReply(id) {
  return request({
    url: `product/reply/fictitious_reply/${id}`,
    method: 'get',
  });
}

/**
 * @description 商品属性 -- 获取规则属性模板
 */
export function productGetRuleApi() {
  return request({
    url: `product/product/get_rule`,
    method: 'get',
  });
}

/**
 * @description 商品 -- 获取运费模板
 */
export function productGetTemplateApi() {
  return request({
    url: `product/product/get_template`,
    method: 'get',
  });
}

/**
 * @description 商品 -- 获取运费模板
 */
export function productGetTempKeysApi() {
  return request({
    url: `product/product/get_temp_keys`,
    method: 'get',
  });
}

/**
 * @description 商铺产品 -- 导出
 */
export function storeProductApi(data) {
  return request({
    url: `export/storeProduct`,
    method: 'get',
    params: data,
  });
}

/**
 * @description 添加商品 -- 检测活动存在
 */
export function checkActivityApi(id) {
  return request({
    url: `product/product/check_activity/${id}`,
    method: 'get',
  });
}

/**
 * @description 商品添加编辑-- 用户标签
 */
export function labelListApi() {
  return request({
    url: 'user/user_label',
    method: 'get',
  });
}
/**
 * @description 组件获取用户标签
 */
export function productUserLabel() {
  return request({
    url: 'user/user_tree_label',
    method: 'get',
  });
}
/**
 * @description 商品添加编辑-- 用户标签
 */
export function uploadType() {
  return request({
    url: 'file/upload_type',
    method: 'get',
  });
}
/**
 * @description 商品添加编辑-- 用户标签
 */
export function importCard(data) {
  return request({
    url: 'product/product/import_card',
    method: 'get',
    params: data,
  });
}

/**
 * @description 商品批量设置
 * @param {Number} param id {Number} 属性id
 * @param {Object} param data {Object} 传值参数
 */
export function batchSetting(data) {
  return request({
    url: `product/batch/setting`,
    method: 'POST',
    data,
  });
}
