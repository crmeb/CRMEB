import request from '@/libs/request';

/* ---------------- AI 模型管理（系统内置 AI 能力） ---------------- */

/**
 * AI 模型列表（分页）
 * @param {Object} params 筛选与分页参数（keyword/protocol/status/page/limit）
 * @returns {Promise} {list, count}
 */
export function aiModelListApi(params) {
  return request({ url: 'ai/model/list', method: 'get', params });
}

/**
 * AI 协议清单（表单渲染协议族选项）
 * @returns {Promise} 协议族数组（protocol/name/description）
 */
export function aiModelProtocolsApi() {
  return request({ url: 'ai/model/protocols', method: 'get' });
}

/**
 * AI 模型详情（密钥脱敏，仅返回 has_api_key 布尔）
 * @param {Number} id 模型 ID
 * @returns {Promise} 模型记录
 */
export function aiModelDetailApi(id) {
  return request({ url: `ai/model/detail/${id}`, method: 'get' });
}

/**
 * 新增 AI 模型
 * @param {Object} data 模型表单数据
 * @returns {Promise}
 */
export function aiModelSaveApi(data) {
  return request({ url: 'ai/model/save', method: 'post', data });
}

/**
 * AI 模型连通性测试（发起一次真实对话请求，不落库）
 * @param {Object} data 模型表单数据（可为已保存或待保存的配置）
 * @returns {Promise} {ok, cost_ms, message}
 */
export function aiModelTestApi(data) {
  return request({ url: 'ai/model/test', method: 'post', data });
}

/**
 * 修改 AI 模型
 * @param {Number} id 模型 ID
 * @param {Object} data 模型表单数据
 * @returns {Promise}
 */
export function aiModelUpdateApi(id, data) {
  return request({ url: `ai/model/update/${id}`, method: 'put', data });
}

/**
 * 删除 AI 模型
 * @param {Number} id 模型 ID
 * @returns {Promise}
 */
export function aiModelDeleteApi(id) {
  return request({ url: `ai/model/delete/${id}`, method: 'delete' });
}

/**
 * 修改 AI 模型启停状态
 * @param {Number} id 模型 ID
 * @param {Number} status 0=停用 1=启用
 * @returns {Promise}
 */
export function aiModelStatusApi(id, status) {
  return request({ url: `ai/model/set_status/${id}`, method: 'put', data: { status } });
}

/**
 * 设置系统默认 AI 模型（id=0 表示切回内置一号通）
 * @param {Number} id 模型 ID
 * @returns {Promise}
 */
export function aiModelSetDefaultApi(id) {
  return request({ url: `ai/model/set_default/${id}`, method: 'put' });
}
