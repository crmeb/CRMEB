<template>
  <div class="aiagent-page">
    <div class="chat-layout">
      <!-- 左侧：导航 + 会话列表 -->
      <ConversationPanel
        :conversations="conversations"
        :loading="loadingConversations"
        :active-id="conversationId"
        @create="newConversation"
        @select="selectConversation"
        @remove="removeConversation"
        @pin="pinConversation"
        @rename="renameConversation"
        @nav="openManage"
      />

      <!-- 右侧：消息区 + 输入区（管理页遮盖层覆盖于此） -->
      <section class="chat-main">
        <div v-if="activeManage" class="manage-overlay">
          <div class="manage-header">
            <button class="manage-back" type="button" @click="closeManage">
              <i class="el-icon-back" />
              <span>返回</span>
            </button>
            <span class="manage-title">{{ manageTitles[activeManage] }}</span>
          </div>
          <div class="manage-body">
            <component :is="manageComponent" />
          </div>
        </div>

        <div ref="messageBox" class="message-list" @click="onMessageBoxClick">
          <div v-if="!messages.length" class="empty-chat">
            <div class="empty-title">今天帮你做些什么？</div>
            <div class="empty-tip">点击输入框右下角模型名称可切换 AI 模型</div>
          </div>

          <template v-for="(message, index) in messages">
            <!-- 用户消息：右侧灰气泡 -->
            <div v-if="message.role === 'user'" :key="index" class="user-row">
              <div class="user-msg">
                <div class="user-bubble">{{ message.content }}</div>
                <div class="user-actions">
                  <el-tooltip content="复制" placement="top">
                    <i class="el-icon-document-copy" @click="copyMessage(message.content)" />
                  </el-tooltip>
                  <el-tooltip content="编辑" placement="top">
                    <i class="el-icon-edit" @click="editMessage(message.content)" />
                  </el-tooltip>
                </div>
              </div>
            </div>

            <!-- 问询卡片：AI 发起单选/多选/文本提问 -->
            <AskCard
              v-else-if="message.role === 'confirm' && message.interaction === 'ask_user'"
              :key="'c' + index"
              :message="message"
              :chatting="chatting"
              @resolve="resolveConfirm"
            />

            <!-- 执行确认卡片：需确认工具被拦截，等待用户决定 -->
            <ConfirmCard
              v-else-if="message.role === 'confirm'"
              :key="'c' + index"
              :message="message"
              :chatting="chatting"
              @resolve="resolveConfirm"
            />

            <!-- AI 回复：通栏排版 -->
            <div v-else :key="'a' + index" class="assistant-row">
              <!-- 系统过程（深度思考）：思维链、调用过的 Skill 与工具痕迹，默认折叠，展开才可见 -->
              <div
                v-if="
                  message.reasoning ||
                  (message.skills && message.skills.length) ||
                  (message.tool_calls && message.tool_calls.length)
                "
                class="reasoning-block"
              >
                <div class="reasoning-toggle" @click="message.reasoningOpen = !message.reasoningOpen">
                  <span>深度思考</span>
                  <el-icon>
                    <component :is="message.reasoningOpen ? 'CaretBottom' : 'CaretRight'" />
                  </el-icon>
                </div>
                <div v-show="message.reasoningOpen" class="reasoning-content">
                  <!-- trimEnd：思维链文本尾部换行经 pre-wrap 渲染成大片空白，去掉尾部空白再显示 -->
                  {{ message.reasoning.trimEnd() }}
                  <div v-if="message.skills && message.skills.length" class="skill-trace">
                    <span v-for="skill in message.skills" :key="skill.key || skill" class="trace-tag"
                      >Skill {{ skill.name || skill }}</span
                    >
                  </div>
                  <div v-if="message.tool_calls && message.tool_calls.length" class="skill-trace">
                    <span v-for="(tool, i) in message.tool_calls" :key="i" class="trace-tag">调用 {{ tool.name }}</span>
                  </div>
                </div>
              </div>
              <div
                v-if="message.content"
                class="assistant-content markdown-body"
                v-html="renderMarkdown(message.content)"
              ></div>
              <div v-else-if="message.streaming" class="assistant-content typing">
                <i class="el-icon-loading" /> 正在思考…
              </div>
              <!-- 消息底部操作区：复制 / 重新生成（重新生成仅最后一条回复开放），置于推荐问题上方；
                   流式进行中不展示操作区，终止输出走输入区发送位（已变停止按钮） -->
              <div v-if="message.content && !message.streaming" class="assistant-actions">
                <el-tooltip content="复制" placement="top">
                  <el-icon class="action-icon" @click="copyMessage(message.content)"><CopyDocument /></el-icon>
                </el-tooltip>
                <el-tooltip v-if="isLastReply(message)" content="重新生成" placement="top">
                  <el-icon class="action-icon" @click="regenerateMessage(message)"><RefreshRight /></el-icon>
                </el-tooltip>
                <!-- 回复时间：历史消息取后端 create_time（秒级时间戳），实时回复在流式结束时记录 -->
                <span v-if="message.create_time" class="action-time">{{ formatTime(message.create_time) }}</span>
              </div>
              <!-- 推荐追问：回答完成后展示，点击直接发送 -->
              <div v-if="!message.streaming && message.followups && message.followups.length" class="followups">
                <button
                  v-for="question in message.followups"
                  :key="question"
                  class="followup-tag"
                  type="button"
                  :disabled="chatting"
                  @click="sendMessage(question)"
                >
                  {{ question }}
                </button>
              </div>
            </div>
          </template>
        </div>

        <!-- 输入区：快捷场景标签 + 输入卡片 -->
        <ChatInput
          ref="chatInputRef"
          v-model:input="chatInput"
          v-model:server-ids="serverIds"
          v-model:skill-keys="skillKeys"
          :topics="topics"
          :models="options.models"
          :mcp-servers="options.mcp_servers || []"
          :skills="options.skills || []"
          :model-id="modelId"
          :chatting="chatting"
          @send="sendMessage()"
          @stop="stopStreaming"
          @select-model="selectModel"
          @manage="openManage"
        />
      </section>
    </div>
  </div>
</template>

<script setup>
import { computed, markRaw, nextTick, onMounted, ref } from 'vue';
import { ElMessage, ElMessageBox } from 'element-plus';
import { useRoute, useRouter } from 'vue-router';
import setting from '@/setting';
import {
  aiAgentChatStreamApi,
  aiAgentConfirmStreamApi,
  aiAgentConversationsApi,
  aiAgentDeleteConversationApi,
  aiAgentMessagesApi,
  aiAgentOptionsApi,
  aiAgentUpdateConversationApi,
} from '../api';
import { renderMarkdown } from '../utils/markdown';
import { useChatStream } from '../composables/useChatStream';
import ConversationPanel from '../components/ConversationPanel.vue';
import AskCard from '../components/AskCard.vue';
import ConfirmCard from '../components/ConfirmCard.vue';
import ChatInput from '../components/ChatInput.vue';
import McpManage from '../components/McpManage.vue';
import SkillManage from '../components/SkillManage.vue';
import TaskManage from '../components/TaskManage.vue';

defineOptions({ name: 'AiAgentChat' });

const router = useRouter();
const route = useRoute();
const messageBox = ref(null);
const chatInputRef = ref(null);

/** 会话与消息状态 */
const conversations = ref([]);
const loadingConversations = ref(false);
const conversationId = ref(0);
const messages = ref([]);
const chatInput = ref('');
const modelId = ref(0);
/** 本轮对话显式挂载的连接器/Skill（空数组=全部挂载/按需加载） */
const serverIds = ref([]);
const skillKeys = ref([]);
const options = ref({ models: [] });

/** 快捷场景标签：点击仅回填输入框（icon 为全局注册的 @element-plus/icons-vue 组件名） */
const topics = [
  { label: '系统设置', icon: 'Setting', prompt: '帮我分析当前系统设置，有哪些配置值得优化？' },
  { label: '用户分析', icon: 'User', prompt: '帮我分析平台的用户数据：用户规模、增长趋势与活跃情况' },
  { label: '财务分析', icon: 'Money', prompt: '帮我分析近期财务状况：营业额、订单量与退款情况' },
  { label: '商品分析', icon: 'Goods', prompt: '帮我分析商品销售情况：热销商品与库存现状' },
];

/** 管理遮盖层：MCP / Skill / 自动化任务（模型管理已内置为系统页面，入口直接跳转） */
const activeManage = ref('');
const manageTitles = { mcp: 'MCP 管理', skill: 'Skill 管理', task: '自动化任务' };
const manageComponents = markRaw({ mcp: McpManage, skill: SkillManage, task: TaskManage });
const manageComponent = computed(() => manageComponents[activeManage.value]);

/** 流式请求状态与 SSE 事件处理（对话与确认操作共用） */
const { chatting, newReply, finishReply, handleStreamEvent } = useChatStream({
  messages,
  conversationId,
  onScroll: scrollToBottom,
});

/** 当前流式请求的中止控制器（手动终止输出用，非响应式） */
let streamController = null;

onMounted(() => {
  loadOptions(true);
  loadConversations(true);
});

function loadOptions(applyDefault = false) {
  aiAgentOptionsApi().then((res) => {
    options.value = { ...options.value, ...(res.data || {}) };
    // 首次加载时选中"默认模型"（is_default 全局唯一：登记默认或一号通）
    if (applyDefault) {
      const def = (options.value.models || []).find((model) => model.is_default === 1);
      if (def) modelId.value = def.id;
    }
  });
}

/** 加载会话列表
 * @param {Boolean} autoSelect 进入页面时自动选中最近一次会话（删除会话后的刷新不触发）；
 *   URL 携带 conversation_id 时（如任务运行详情「在对话中查看」）优先定位该会话
 */
function loadConversations(autoSelect = false) {
  loadingConversations.value = true;
  aiAgentConversationsApi()
    .then((res) => {
      conversations.value = res.data || [];
      const queryId = Number(route.query.conversation_id || 0);
      if (autoSelect && !conversationId.value && conversations.value.length) {
        const target = queryId > 0 ? conversations.value.find((item) => item.id === queryId) : null;
        selectConversation(target || conversations.value[0]);
      }
    })
    .finally(() => {
      loadingConversations.value = false;
    });
}

function newConversation() {
  // 从管理页（MCP/Skill 遮盖层）发起新建时，需关闭遮盖层回到对话区
  activeManage.value = '';
  conversationId.value = 0;
  messages.value = [];
  chatInput.value = '';
}

function selectConversation(item, force = false) {
  // 从管理遮盖层点击会话记录时，关闭遮盖层展示对话内容
  activeManage.value = '';
  if (!force && conversationId.value === item.id) return;
  conversationId.value = item.id;
  aiAgentMessagesApi(item.id).then((res) => {
    if (conversationId.value !== item.id) return;
    // 过滤中间过程消息（tool 角色与带 tool_name 的 assistant 工具调用记录），仅展示问答内容与交互卡片
    messages.value = (res.data || [])
      .filter((message) => message.role !== 'tool' && (message.role === 'confirm' || !message.tool_name))
      .map((message) => {
        // 交互卡片：tool_payload 承载参数快照、交互类型与确认状态（问询交互态由卡片组件自持）
        if (message.role === 'confirm') {
          const payload = message.tool_payload || {};
          return {
            role: 'confirm',
            messageId: message.id,
            toolName: message.tool_name,
            arguments: payload.arguments || {},
            interaction: payload.interaction || 'execute',
            display: payload.display || null,
            status: payload.status || 'pending',
          };
        }
        return {
          ...message,
          reasoning: message.tool_payload?.reasoning || '',
          reasoningOpen: false,
          skills: message.tool_payload?.skills || [],
          followups: message.tool_payload?.followups || [],
          // 历史消息回显工具调用标签（只展示工具名；旧数据中的 label 字段忽略，参数与结果不入库）
          tool_calls: (message.tool_payload?.tool_calls || [])
            .map((item) => ({ name: item?.name || '' }))
            .filter((item) => item.name),
        };
      });
    scrollToBottom();
  });
}

function removeConversation(item) {
  ElMessageBox.confirm('确定删除该会话吗？', '提示', { type: 'warning' })
    .then(() => aiAgentDeleteConversationApi(item.id))
    .then(() => {
      ElMessage.success('删除成功');
      if (conversationId.value === item.id) newConversation();
      loadConversations();
    })
    .catch(() => {});
}

/** 置顶/取消置顶会话 */
function pinConversation(item) {
  const pin = item.is_pin === 1 ? 0 : 1;
  aiAgentUpdateConversationApi(item.id, { is_pin: pin }).then(() => {
    ElMessage.success(pin ? '已置顶' : '已取消置顶');
    loadConversations();
  });
}

/** 重命名会话标题 */
function renameConversation(item) {
  ElMessageBox.prompt('请输入新的会话标题', '重命名会话', {
    inputValue: item.title,
    inputValidator: (value) => (value && value.trim() ? true : '标题不能为空'),
  })
    .then(({ value }) => aiAgentUpdateConversationApi(item.id, { title: value.trim() }))
    .then(() => {
      ElMessage.success('修改成功');
      loadConversations();
    })
    .catch(() => {});
}

/**
 * 发送消息：不传参从输入框取内容并发送后清空；传入字符串（推荐追问）直接作为消息发送
 * @param {String|undefined} question 可选的待发送问题
 */
function sendMessage(question) {
  const fromInput = typeof question !== 'string';
  const content = (fromInput ? chatInput.value : question).trim();
  if (!content || chatting.value) return;
  messages.value.push({ role: 'user', content });
  if (fromInput) {
    chatInput.value = '';
  }
  startStream(content);
}

/** 发起流式对话：创建回复占位并建立 SSE 连接（重新生成复用） */
function startStream(content) {
  chatting.value = true;
  const reply = newReply();
  messages.value.push(reply);
  scrollToBottom();
  streamController = new AbortController();
  aiAgentChatStreamApi(
    {
      conversation_id: conversationId.value,
      message: content,
      model_id: modelId.value,
      mcp_server_ids: serverIds.value,
      skill_keys: skillKeys.value,
    },
    (event) => handleStreamEvent(event, reply),
    { signal: streamController.signal },
  )
    .then(() => {
      streamController = null;
      finishReply(reply);
      loadConversations();
    })
    .catch((e) => {
      streamController = null;
      if (e?.name === 'AbortError') {
        finishAborted(reply);
      } else {
        ElMessage.error(e.message || '对话失败');
        finishReply(reply);
        if (conversationId.value) selectConversation({ id: conversationId.value }, true);
        loadConversations();
      }
    });
}

/** 手动终止当前流式输出（保留已生成内容，后端随连接断开停止生成） */
function stopStreaming() {
  if (streamController) {
    streamController.abort();
    streamController = null;
  }
}

/** 手动终止后的收尾：保留已输出内容，正文为空时补占位提示，避免用户消息悬空无回应 */
function finishAborted(reply) {
  if (!reply.content) {
    reply.content = '已手动终止本次输出。';
  }
  finishReply(reply);
}

/** 是否为最后一条 AI 回复（重新生成仅对最后一条回复开放） */
function isLastReply(message) {
  const list = messages.value;
  return list[list.length - 1] === message;
}

/**
 * 重新生成：取该回复前最近的用户消息原样重发，移除旧回复后原地重新流式生成（用户气泡保留）
 * @param {Object} message 待重新生成的 AI 回复
 */
function regenerateMessage(message) {
  if (chatting.value || !isLastReply(message)) return;
  const list = messages.value;
  const index = list.indexOf(message);
  let question = '';
  for (let i = index - 1; i >= 0; i--) {
    if (list[i].role === 'user') {
      question = list[i].content;
      break;
    }
  }
  if (!question) return;
  list.splice(index, 1);
  startStream(question);
}

/** 用户处理交互卡片：execute 类确认执行/取消；ask_user 类提交答案/跳过（结果以流式响应继续渲染） */
function resolveConfirm(message, action, extra = {}) {
  if (chatting.value || message.status !== 'pending') return;
  chatting.value = true;
  // 乐观更新卡片状态，失败时回滚
  message.status = action === 'approve' ? 'approved' : 'rejected';
  const reply = newReply();
  messages.value.push(reply);
  scrollToBottom();
  streamController = new AbortController();
  aiAgentConfirmStreamApi(
    {
      conversation_id: conversationId.value,
      message_id: message.messageId,
      action,
      answers: extra.answers || [],
      custom: extra.custom || '',
    },
    (event) => handleStreamEvent(event, reply),
    { signal: streamController.signal },
  )
    .then(() => {
      streamController = null;
      finishReply(reply);
      loadConversations();
    })
    .catch((e) => {
      streamController = null;
      if (e?.name === 'AbortError') {
        // 手动终止：卡片决定已提交，保持当前状态防止重复执行，仅停止后续输出
        finishAborted(reply);
      } else {
        ElMessage.error(e.message || '操作失败');
        finishReply(reply);
        // 状态可能已落库（确认后模型续跑失败），回读服务端记录以免重复执行。
        if (conversationId.value) selectConversation({ id: conversationId.value }, true);
        loadConversations();
      }
    });
}

/** 编辑消息：内容回填输入框并聚焦 */
function editMessage(content) {
  chatInput.value = content;
  chatInputRef.value?.focus();
}

/**
 * 将接口返回的秒级时间戳、毫秒级时间戳或日期字符串统一转换为毫秒级时间戳。
 * 历史消息的数据字段可能来自不同版本，不能假设 create_time 始终为秒级数字。
 */
function toTimestamp(time) {
  if (time === null || time === undefined || time === '') return 0;

  const numericTime = Number(time);
  if (Number.isFinite(numericTime) && String(time).trim() !== '') {
    return numericTime < 100000000000 ? numericTime * 1000 : numericTime;
  }

  return Date.parse(String(time).trim().replace(' ', 'T')) || 0;
}

/** 格式化回复时间：当天/昨天/前天用相对描述，更早显示完整日期 */
function formatTime(timestamp) {
  const normalizedTimestamp = toTimestamp(timestamp);
  if (!normalizedTimestamp) return '';

  const date = new Date(normalizedTimestamp);
  if (Number.isNaN(date.getTime())) return '';

  const pad = (n) => String(n).padStart(2, '0');
  const hhmm = `${pad(date.getHours())}:${pad(date.getMinutes())}`;
  // 以自然日零点为界计算相差天数，避免"不足 24 小时也算昨天"的歧义
  const startOfDay = (d) => new Date(d.getFullYear(), d.getMonth(), d.getDate()).getTime();
  const dayDiff = Math.round((startOfDay(new Date()) - startOfDay(date)) / 86400000);
  const labels = ['今天', '昨天', '前天'];
  if (dayDiff >= 0 && dayDiff < labels.length) return `${labels[dayDiff]} ${hhmm}`;
  // 本年省略年份，跨年才显示完整日期
  const md = `${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
  if (date.getFullYear() === new Date().getFullYear()) return `${md} ${hhmm}`;
  return `${date.getFullYear()}-${md} ${hhmm}`;
}

/** 复制消息内容到剪贴板 */
function copyMessage(content) {
  if (navigator.clipboard) {
    navigator.clipboard
      .writeText(content)
      .then(() => ElMessage.success('已复制'))
      .catch(() => fallbackCopy(content));
  } else {
    fallbackCopy(content);
  }
}

/** 剪贴板 API 不可用时的降级复制 */
function fallbackCopy(content) {
  const textarea = document.createElement('textarea');
  textarea.value = content;
  document.body.appendChild(textarea);
  textarea.select();
  document.execCommand('copy');
  document.body.removeChild(textarea);
  ElMessage.success('已复制');
}

/** 切换当前对话使用的模型 */
function selectModel(model) {
  modelId.value = model.id;
}

/** 打开管理遮盖层（MCP/Skill/任务）；模型管理已内置为系统页面，跳转「设置 → 模型管理」（输入框「配置自定义模型」入口） */
function openManage(target) {
  if (typeof target !== 'string') target = 'model';
  if (target === 'model') {
    router.push(setting.routePre + '/setting/ai_model/index');
    return;
  }
  activeManage.value = target;
}

/** 关闭管理遮盖层（管理页可能改了模型/连接器配置，刷新选项） */
function closeManage() {
  activeManage.value = '';
  loadOptions();
}

/** 消息区滚动到底部 */
function scrollToBottom() {
  nextTick(() => {
    const box = messageBox.value;
    if (box) box.scrollTop = box.scrollHeight;
  });
}

/**
 * 消息内链接点击委托：AI 回复 Markdown 渲染的站内链接（/admin 开头）转路由跳转，
 * 避免原生 <a> 导航整页刷新；外链与无 href 的锚点保持默认行为
 */
function onMessageBoxClick(e) {
  const anchor = e.target.closest('a');
  if (!anchor) return;
  const href = anchor.getAttribute('href') || '';
  if (!href.startsWith('/admin')) return;
  e.preventDefault();
  router.push(href);
}
</script>

<style scoped lang="scss">
.aiagent-page {
  padding-bottom: 20px;
}
.chat-layout {
  display: flex;
  height: calc(100vh - 110px);
  min-height: 480px;
  margin-top: 0;
  overflow: hidden;
  border: 1px solid #ebeef5;
  border-radius: 12px;
}
.chat-main {
  position: relative;
  display: flex;
  flex: 1;
  min-width: 0;
  flex-direction: column;
  background: #fff;
}
/* 管理页遮盖层：覆盖右侧对话区，左侧导航仍可切换 */
.manage-overlay {
  position: absolute;
  inset: 0;
  z-index: 20;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  background: #fff;
}
.manage-header {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 12px 20px;
  border-bottom: 1px solid #ebeef5;
}
.manage-back {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 6px 12px;
  color: #606266;
  font-size: 13px;
  cursor: pointer;
  background: #f4f4f5;
  border: none;
  border-radius: 16px;
  &:hover {
    color: var(--prev-color-primary);
    background: #e9e9eb;
  }
}
.manage-title {
  color: #303133;
  font-size: 15px;
  font-weight: 600;
}
.manage-body {
  flex: 1;
  padding: 0 20px 20px;
  overflow-y: auto;
}
.message-list {
  /* flex:1 纵向撑满使输入区贴底；宽屏时水平限宽居中，避免通栏排版被拉得过长 */
  flex: 1;
  width: 100%;
  max-width: 900px;
  margin: 0 auto;
  padding: 24px 32px;
  overflow-y: auto;
}
.empty-chat {
  margin-top: 22vh;
  text-align: center;
}
.empty-title {
  color: #303133;
  font-size: 22px;
  font-weight: 600;
}
.empty-tip {
  margin-top: 10px;
  color: #a8abb2;
  font-size: 13px;
}
/* 用户消息：右侧浅灰气泡 */
.user-row {
  display: flex;
  justify-content: flex-end;
  margin-bottom: 26px;
}
.user-msg {
  display: flex;
  max-width: 68%;
  flex-direction: column;
  align-items: flex-end;
}
.user-bubble {
  padding: 12px 18px;
  color: #303133;
  line-height: 1.65;
  white-space: pre-wrap;
  word-break: break-word;
  background: #f2f3f5;
  border-radius: 18px;
}
/* 气泡下方操作图标：hover 时浮现 */
.user-actions {
  display: flex;
  gap: 14px;
  margin-top: 6px;
  padding-right: 6px;
  color: #a8abb2;
  font-size: 14px;
  opacity: 0;
  transition: opacity 0.15s;
  i {
    cursor: pointer;
    &:hover {
      color: var(--prev-color-primary);
    }
  }
}
.user-row:hover .user-actions {
  opacity: 1;
}
/* AI 回复：通栏排版 */
.assistant-row {
  margin-bottom: 30px;
}
/* 深度思考折叠块：小字切换 + 左侧竖线引用样式 */
.reasoning-block {
  margin-bottom: 12px;
}
.reasoning-toggle {
  display: inline-flex;
  align-items: center;
  gap: 2px;
  color: #9aa0a8;
  font-size: 13px;
  cursor: pointer;
  user-select: none;
  i {
    font-size: 12px;
  }
  &:hover {
    color: #606266;
  }
}
.reasoning-content {
  margin-top: 6px;
  padding: 4px 0 4px 16px;
  color: #9aa0a8;
  font-size: 13px;
  line-height: 1.9;
  white-space: pre-wrap;
  word-break: break-word;
  border-left: 3px solid #e3e5e8;
}
/* 系统过程内调用的 Skill 标签：与思维链文本区分 */
.skill-trace {
  display: flex;
  gap: 6px;
  margin-top: 10px;
  flex-wrap: wrap;
}
/* 过程痕迹信息签（Skill / 工具调用）：纯展示，浅灰禁用态避免误导可点击（不用 el-tag，防主题桥接染成蓝色） */
.trace-tag {
  display: inline-block;
  height: 20px;
  line-height: 18px;
  padding: 0 6px;
  font-size: 12px;
  color: #909399;
  background: #f7f8fa;
  border: 1px solid #e9e9eb;
  border-radius: 4px;
  cursor: default;
  user-select: none;
}
.assistant-content {
  color: #303133;
  font-size: 14px;
  line-height: 1.75;
  word-break: break-word;

  :deep(p) {
    margin: 0 0 12px;
  }
  :deep(p:last-child) {
    margin-bottom: 0;
  }
  :deep(h1),
  :deep(h2),
  :deep(h3),
  :deep(h4),
  :deep(h5),
  :deep(h6) {
    margin: 20px 0 10px;
    color: #1f2937;
    line-height: 1.4;
  }
  :deep(hr) {
    height: 0;
    margin: 16px 0;
    border: 0;
    border-top: 1px solid #e4e7ed;
  }
  :deep(ul),
  :deep(ol) {
    margin: 0 0 12px;
    padding-left: 24px;
  }
  :deep(blockquote) {
    margin: 12px 0;
    padding: 4px 12px;
    color: #606266;
    border-left: 3px solid #dcdfe6;
  }
  :deep(pre) {
    margin: 12px 0;
    padding: 12px;
    overflow-x: auto;
    color: #303133;
    white-space: pre-wrap;
    background: #f5f7fa;
    border-radius: 6px;
  }
  :deep(code) {
    padding: 2px 4px;
    font-family: Consolas, Monaco, monospace;
    font-size: 0.9em;
    background: #f5f7fa;
    border-radius: 3px;
  }
  :deep(pre code) {
    padding: 0;
    background: transparent;
  }
  :deep(table) {
    width: 100%;
    margin: 12px 0;
    border-collapse: collapse;
  }
  :deep(th),
  :deep(td) {
    padding: 8px 10px;
    text-align: left;
    border: 1px solid #ebeef5;
  }
  :deep(th) {
    background: #f5f7fa;
  }
  :deep(a) {
    color: var(--prev-color-primary);
  }
}
.assistant-content.typing {
  color: #909399;
}
/* 推荐追问：回答完成后展示的胶囊标签，点击直接发送 */
.followups {
  display: flex;
  gap: 8px;
  margin-top: 14px;
  flex-wrap: wrap;
}
.followup-tag {
  padding: 6px 14px;
  color: #606266;
  font-size: 13px;
  line-height: 1.5;
  text-align: left;
  cursor: pointer;
  word-break: break-all;
  background: #f7f8fa;
  border: 1px solid #e4e7ed;
  border-radius: 999px;
  transition: color 0.15s, border-color 0.15s, background 0.15s;
  &:hover:not(:disabled) {
    color: var(--prev-color-primary);
    background: #f0f7ff;
    border-color: var(--prev-color-primary);
  }
  &:disabled {
    color: #c0c4cc;
    cursor: not-allowed;
  }
}
/* 消息底部操作区：流式中的终止按钮 / 完成后的复制与重新生成图标 */
.assistant-actions {
  display: flex;
  align-items: center;
  gap: 14px;
  margin-top: 12px;
  color: #a8abb2;
  font-size: 14px;
}
.action-icon {
  cursor: pointer;
  transition: color 0.15s;
  &:hover {
    color: var(--prev-color-primary);
  }
}
/* 回复完成时间：弱化展示，与操作图标同行对齐 */
.action-time {
  font-size: 12px;
  color: #c0c4cc;
}
</style>
