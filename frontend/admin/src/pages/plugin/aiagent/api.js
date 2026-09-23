import request from '@/libs/request';
import Setting from '@/setting';
import { getCookies } from '@/libs/util';

export function aiAgentOptionsApi() {
  return request({ url: 'aiagent/options', method: 'get' });
}

export function aiAgentConversationsApi() {
  return request({ url: 'aiagent/conversations', method: 'get' });
}

export function aiAgentMessagesApi(conversationId) {
  return request({ url: `aiagent/messages/${conversationId}`, method: 'get' });
}

export function aiAgentDeleteConversationApi(id) {
  return request({ url: `aiagent/conversation/${id}`, method: 'delete' });
}

/**
 * 修改会话（标题/置顶，title 与 is_pin 至少传一项）
 * @param {Number} id 会话 ID
 * @param {Object} data {title: 新标题，is_pin: -1 不修改 / 0 取消置顶 / 1 置顶}
 * @returns {Promise}
 */
export function aiAgentUpdateConversationApi(id, data) {
  return request({ url: `aiagent/conversation/${id}`, method: 'put', data });
}

/**
 * AI 流式对话（SSE）：通过 fetch 读取字节流逐块解析，事件实时回调
 * @param {Object} data 对话参数（conversation_id / message / model_id）
 * @param {Function} onEvent 事件回调 function(event)，event = {event: 'start'|'reasoning'|'delta'|'replace'|'tool'|'confirm'|'done'|'error', ...}
 * @param {Object} [options] 额外选项（signal: AbortSignal，用于手动终止流式输出）
 * @returns {Promise<void>} 流读取完毕后 resolve
 */
export function aiAgentChatStreamApi(data, onEvent, options = {}) {
  return sseRequest('aiagent/chat_stream', data, onEvent, { ...options, requireDone: true });
}

/**
 * AI 待确认操作处理（SSE）：approve=按快照执行并继续对话循环 / reject=取消
 * @param {Object} data 参数（conversation_id / message_id / action）
 * @param {Function} onEvent 事件回调，事件类型与 chat_stream 一致
 * @param {Object} [options] 额外选项（signal: AbortSignal，用于手动终止流式输出）
 * @returns {Promise<void>} 流读取完毕后 resolve
 */
export function aiAgentConfirmStreamApi(data, onEvent, options = {}) {
  return sseRequest('aiagent/confirm_stream', data, onEvent, { ...options, requireDone: true });
}

/**
 * SSE POST 通用封装：fetch 读取字节流逐块解析，事件实时回调。
 * 插件内所有流式接口复用（对话 / 确认操作 / AI 写作弹窗）。
 * @param {String} path 接口路径（相对 adminapi）
 * @param {Object} data 请求参数
 * @param {Function} onEvent 事件回调 function(event)，event 为反序列化后的 JSON 对象
 * @param {Object} [options] 额外选项（signal: AbortSignal）
 * @returns {Promise<void>}
 */
export function sseRequest(path, data, onEvent, options = {}) {
  const base = Setting.apiBaseURL.replace(/\/$/, '');
  return fetch(`${base}/${path}`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authori-zation': 'Bearer ' + (getCookies('token') || ''),
    },
    body: JSON.stringify(data),
    signal: options.signal,
  }).then(async (response) => {
    if (!response.ok || !response.body) {
      if (response.status === 401) throw new Error('登录已失效，请重新登录');
      if (response.status === 403) throw new Error('暂无操作权限');
      throw new Error('流式请求失败：HTTP ' + response.status);
    }
    let finished = false;
    // 解析单个 SSE 事件块：非法 JSON 跳过，业务错误必须传递给请求方。
    const emitBlock = (block) => {
      const line = block.split('\n').find((item) => item.startsWith('data:'));
      if (!line) return;
      let event;
      try {
        event = JSON.parse(line.slice(5).trim());
      } catch (e) {
        return;
      }
      onEvent(event);
      if (event.event === 'done') finished = true;
      if (options.requireDone && event.event === 'error') throw new Error(event.msg || '对话失败');
    };
    const reader = response.body.getReader();
    const decoder = new TextDecoder('utf-8');
    let buffer = '';
    for (;;) {
      const { done, value } = await reader.read();
      if (done) break;
      buffer += decoder.decode(value, { stream: true });
      let index;
      while ((index = buffer.indexOf('\n\n')) !== -1) {
        const block = buffer.slice(0, index);
        buffer = buffer.slice(index + 2);
        emitBlock(block);
      }
    }
    // 尾部兜底：流结束时残留 buffer（末尾事件缺空行分隔符）也参与解析，避免静默丢弃
    if (buffer.trim()) emitBlock(buffer);
    if (options.requireDone && !finished) throw new Error('对话连接已中断，请检查会话记录后重试');
  });
}

/* ---------------- MCP Server 管理 ---------------- */

export function aiMcpServerListApi() {
  return request({ url: 'aiagent/mcp_servers', method: 'get' });
}

export function aiMcpServerDetailApi(id) {
  return request({ url: `aiagent/mcp_server/${id}`, method: 'get' });
}

export function aiMcpServerSaveApi(data, id = 0) {
  return request({
    url: id ? `aiagent/mcp_server/${id}` : 'aiagent/mcp_server',
    method: id ? 'put' : 'post',
    data,
  });
}

export function aiMcpServerDeleteApi(id) {
  return request({ url: `aiagent/mcp_server/${id}`, method: 'delete' });
}

export function aiMcpServerStatusApi(id, status) {
  return request({ url: `aiagent/mcp_server/${id}/status`, method: 'put', data: { status } });
}

export function aiMcpServerSyncApi(id) {
  return request({ url: `aiagent/mcp_server/${id}/sync`, method: 'post' });
}

export function aiMcpServerTestApi(id) {
  return request({ url: `aiagent/mcp_server/${id}/test`, method: 'post' });
}

/* ---------------- Skill 管理 ---------------- */

/**
 * AI 对话（非流式，普通 JSON 返回）
 * @param {String} prompt 用户提示词
 * @param {Array} prompts 系统提示词数组
 * @returns {Promise} {content, model}
 */
export function aiChatApi(prompt, prompts = []) {
  return request({ url: 'ai/chat', method: 'post', data: { prompt, prompts } });
}

export function aiSkillListApi() {
  return request({ url: 'aiagent/skills', method: 'get' });
}

export function aiSkillDetailApi(key) {
  return request({ url: `aiagent/skill/${key}`, method: 'get' });
}

export function aiSkillSaveApi(data) {
  return request({ url: 'aiagent/skill', method: 'post', data });
}

export function aiSkillUpdateApi(key, data) {
  return request({ url: `aiagent/skill/${key}`, method: 'put', data });
}

export function aiSkillDeleteApi(key) {
  return request({ url: `aiagent/skill/${key}`, method: 'delete' });
}

export function aiSkillStatusApi(key, status) {
  return request({ url: `aiagent/skill/${key}/status`, method: 'put', data: { status } });
}

/* ---------------- Skill 附属资料管理（技能目录下的 .md 文件，Agent 经 crmeb_skill_load 按需加载） ---------------- */

/**
 * Skill 附属资料列表
 * @param {String} key Skill 标识
 * @returns {Promise} 附属资料清单（file/title/size/update_time）
 */
export function aiSkillAttachmentListApi(key) {
  return request({ url: `aiagent/skill/${key}/attachments`, method: 'get' });
}

/**
 * Skill 附属资料详情（含全文）
 * @param {String} key Skill 标识
 * @param {String} file 附属资料文件名
 * @returns {Promise} 附件详情（file/title/content/update_time）
 */
export function aiSkillAttachmentReadApi(key, file) {
  return request({ url: `aiagent/skill/${key}/attachment`, method: 'get', params: { file } });
}

/**
 * 保存（新建或覆盖）Skill 附属资料
 * @param {String} key Skill 标识
 * @param {Object} data 附件内容（file/content）
 * @returns {Promise}
 */
export function aiSkillAttachmentSaveApi(key, data) {
  return request({ url: `aiagent/skill/${key}/attachment`, method: 'post', data });
}

/**
 * 删除 Skill 附属资料
 * @param {String} key Skill 标识
 * @param {String} file 附属资料文件名
 * @returns {Promise}
 */
export function aiSkillAttachmentDeleteApi(key, file) {
  return request({ url: `aiagent/skill/${key}/attachment`, method: 'delete', params: { file } });
}

/* ---------------- AI 自动化任务管理 ---------------- */

/**
 * 自动化任务列表（当前管理员，分页）
 * @param {Object} params 分页参数（page/limit）
 * @returns {Promise} {list, count}
 */
export function aiTaskListApi(params) {
  return request({ url: 'aiagent/tasks', method: 'get', params });
}

/**
 * 创建/编辑自动化任务（编辑时 data.id > 0）
 * @param {Object} data 任务表单数据
 * @returns {Promise} {id}
 */
export function aiTaskSaveApi(data) {
  const id = data.id || 0;
  return request({
    url: id ? `aiagent/task/${id}` : 'aiagent/task',
    method: id ? 'put' : 'post',
    data,
  });
}

/**
 * 自动化任务详情
 * @param {Number} id 任务 ID
 * @returns {Promise} 任务记录（JSON 字段已解码）
 */
export function aiTaskDetailApi(id) {
  return request({ url: `aiagent/task/${id}`, method: 'get' });
}

/**
 * 删除自动化任务（含运行历史）
 * @param {Number} id 任务 ID
 * @returns {Promise}
 */
export function aiTaskDeleteApi(id) {
  return request({ url: `aiagent/task/${id}`, method: 'delete' });
}

/**
 * 修改任务启停状态
 * @param {Number} id 任务 ID
 * @param {Number} status 0=停用 1=启用
 * @returns {Promise}
 */
export function aiTaskStatusApi(id, status) {
  return request({ url: `aiagent/task/${id}/status`, method: 'put', data: { status } });
}

/**
 * 立即执行任务（入队，与调度执行同链路）
 * @param {Number} id 任务 ID
 * @returns {Promise} {run_id}
 */
export function aiTaskTriggerApi(id) {
  return request({ url: `aiagent/task/${id}/run`, method: 'post' });
}

/**
 * 任务运行历史（分页）
 * @param {Number} id 任务 ID
 * @param {Object} params 分页参数（page/limit）
 * @returns {Promise} {list, count}
 */
export function aiTaskRunsApi(id, params) {
  return request({ url: `aiagent/task/${id}/runs`, method: 'get', params });
}

/**
 * 全量运行记录（当前管理员的全部任务，分页，含任务名称）
 * @param {Object} params 分页参数（page/limit）
 * @returns {Promise} {list, count}
 */
export function aiTaskRunListApi(params) {
  return request({ url: 'aiagent/task_runs', method: 'get', params });
}

/**
 * 运行详情（含关联会话消息，回放执行全过程）
 * @param {Number} runId 运行记录 ID
 * @returns {Promise} 运行记录 + messages 消息列表
 */
export function aiTaskRunDetailApi(runId) {
  return request({ url: `aiagent/task_run/${runId}`, method: 'get' });
}
