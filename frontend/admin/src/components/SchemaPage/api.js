/**
 * 通用页面网关 API
 *
 * 插件管理页面（SchemaPage 容器）的统一数据入口，
 * 与后端 /adminapi/schema/plugin/{pluginKey}/{pageKey}/* 网关路由一一对应。
 */
import request from '@/libs/request';

/**
 * @description 获取页面结构配置
 */
export function schemaConfigApi(pluginKey, pageKey) {
  return request({ url: `schema/plugin/${pluginKey}/${pageKey}/config`, method: 'get' });
}

/**
 * @description 获取列表数据（分页 + 搜索条件）
 * @param {object} params {page, limit, where: {字段: 值}}
 */
export function schemaListApi(pluginKey, pageKey, params) {
  return request({ url: `schema/plugin/${pluginKey}/${pageKey}/list`, method: 'get', params });
}

/**
 * @description 获取表单规则（id=0 新增，>0 编辑回填）
 */
export function schemaFormApi(pluginKey, pageKey, params) {
  return request({ url: `schema/plugin/${pluginKey}/${pageKey}/form`, method: 'get', params });
}

/**
 * @description 获取详情数据（详情抽屉）
 */
export function schemaDetailApi(pluginKey, pageKey, params) {
  return request({ url: `schema/plugin/${pluginKey}/${pageKey}/detail`, method: 'get', params });
}

/**
 * @description 保存表单（新增/编辑统一入口）
 */
export function schemaSaveApi(pluginKey, pageKey, data) {
  return request({ url: `schema/plugin/${pluginKey}/${pageKey}/save`, method: 'post', data });
}

/**
 * @description 执行通用动作（删除/启停/导出等）
 * @param {object} data {action: 动作标识, payload: 动作参数}
 */
export function schemaActionApi(pluginKey, pageKey, data) {
  return request({ url: `schema/plugin/${pluginKey}/${pageKey}/action`, method: 'post', data });
}
