<template>
  <!-- AI 写作弹窗：Markdown 源码场景专用（与系统富文本 AI 写作同一流式接口，生成结果以纯 Markdown 回传） -->
  <el-dialog
    :model-value="modelValue"
    width="720px"
    title="AI 写作"
    append-to-body
    :close-on-click-modal="false"
    :before-close="beforeClose"
    @update:model-value="(v) => emit('update:modelValue', v)"
  >
    <el-input
      v-model="aiPrompt"
      type="textarea"
      :rows="4"
      maxlength="2000"
      show-word-limit
      :placeholder="promptPlaceholder"
      :disabled="aiGenerating"
    ></el-input>
    <div class="ai-status">
      <span v-if="aiGenerating" class="ai-status-text">
        {{
          aiText
            ? `AI 正在写作，已生成 ${aiText.length} 字…`
            : aiReasoning
            ? `AI 思考中，已思考 ${aiReasoning.length} 字…`
            : 'AI 正在准备…'
        }}
      </span>
      <span v-else-if="aiError" class="ai-status-error">{{ aiError }}</span>
      <span v-else-if="aiText" class="ai-status-tip">
        生成完成，点击「{{ isRewrite ? '替换' : '插入' }}」写入编辑区（Markdown 源码）
      </span>
      <span v-else-if="isRewrite" class="ai-status-tip">请输入修改意见，AI 将基于现有内容改写完善</span>
      <span v-else class="ai-status-tip">点击「开始生成」，AI 内容将在下方实时输出</span>
    </div>
    <div v-if="aiText || aiGenerating" ref="aiPreview" class="ai-preview" v-html="aiPreviewHtml"></div>
    <template #footer>
      <div>
        <template v-if="!aiGenerating">
          <el-button v-db-click @click="close">{{ aiText ? '关闭' : '取消' }}</el-button>
          <el-button v-if="aiText" v-db-click @click="startGenerate">重新生成</el-button>
          <el-button v-if="aiText" type="primary" v-db-click @click="insert">{{
            isRewrite ? '替换' : '插入'
          }}</el-button>
          <el-button v-else type="primary" :loading="aiSubmitting" v-db-click @click="startGenerate">{{
            isRewrite ? '开始优化' : '开始生成'
          }}</el-button>
        </template>
        <el-button v-else type="danger" v-db-click @click="stopGenerate">停止生成</el-button>
      </div>
    </template>
  </el-dialog>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';
import { ElMessage, ElMessageBox } from 'element-plus';
import { sseRequest } from '../api';

defineOptions({ name: 'AiWriteModal' });

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
  // 写作目标提示（用于默认提示词占位与系统提示词，如「Skill 正文」「附件 内容」）
  targetLabel: {
    type: String,
    default: '内容',
  },
  // 写作模式：write=从零撰写；rewrite=基于 sourceText 原文按用户修改意见改写完善
  mode: {
    type: String,
    default: 'write',
  },
  // rewrite 模式的原文（现有正文/附件内容），仅随请求携带，不进提示词输入框
  sourceText: {
    type: String,
    default: '',
  },
  // 附加上下文（如技能名称/描述），随请求携带帮助 AI 理解目标定位；不写入编辑区
  contextText: {
    type: String,
    default: '',
  },
});

const emit = defineEmits(['update:modelValue', 'insert']);

// 默认系统提示词：CRMEB Agent Skill 文档撰写（首行一级标题、面向 AI 助手的规则文体；frontmatter 由后端保存时自动生成，禁止 AI 输出）
const DEFAULT_SYSTEM_PROMPT =
  '你是 CRMEB 管理后台的 Agent Skill 文档助手，根据用户要求直接撰写目标内容。撰写对象是注入 AI 助手的工作规则或按需查阅的资料，面向 AI 助手执行，不是面向人类读者的产品介绍。\n' +
  '格式要求：第一行必须直接是一级标题（# ），标题前禁止任何引导语、寒暄或说明文字；章节用 ## 或 ### 划分；重点用 **加粗**，条目用 - 列表，步骤与对照关系用 | 分隔的表格；直接输出 Markdown 源码正文，不要输出任何解释说明或代码围栏包裹；不要输出 YAML frontmatter（name/description/status 由系统保存时自动生成）。\n' +
  '文体要求：用祈使句描述规则——何时触发、按什么流程做、内容口径是什么、禁止做什么、按什么模板输出；开头用一句话界定适用时机（如「处理 XX 时遵循以下规则：」）。不要写营销价值、功能亮点等介绍性内容。';

// rewrite 模式系统提示词：基于原文与修改意见改写完善，保持标准 Skill 文体
const REWRITE_SYSTEM_PROMPT =
  '你是 CRMEB 管理后台的 Agent Skill 文档优化助手。请基于用户给出的原文与修改意见改写完善目标内容。\n' +
  '改写要求：保留原文中仍正确有效的内容，按修改意见增删调整；保持既有结构与整体文风一致；修改意见与原文冲突时以修改意见为准；修改意见未覆盖的部分不要无故改动。\n' +
  '格式要求：第一行必须直接是一级标题（# ），标题前禁止任何引导语、寒暄或说明文字（如「现在开始输出改写结果」）；章节用 ## 或 ### 划分；重点用 **加粗**，条目用 - 列表，表格用 | 分隔；直接输出改写后的完整 Markdown 源码正文，不要输出任何解释说明或代码围栏包裹；不要输出 YAML frontmatter。\n' +
  '文体要求：内容是注入 AI 助手的工作规则或资料，保持面向 AI 助手执行的祈使句文风，不要改写成面向人类读者的产品介绍。';

// 是否为改写模式（原文非空才生效，防御 sourceText 缺失时自动降级为撰写模式）
const isRewrite = computed(() => props.mode === 'rewrite' && !!props.sourceText.trim());

const promptPlaceholder = computed(() =>
  isRewrite.value
    ? '请输入修改意见，AI 将基于现有内容改写完善，例如：补充 XX 场景、精简操作步骤、改用表格展示'
    : `请输入写作提示词，例如：帮我撰写${props.targetLabel}的 Markdown 正文`,
);

const aiPrompt = ref('');
const aiGenerating = ref(false);
const aiSubmitting = ref(false);
const aiError = ref('');
const aiText = ref('');
// 推理模型思维链增量（后端 reasoning 事件）：仅用于生成中状态展示，不进入最终结果
const aiReasoning = ref('');
const aiPreviewHtml = ref('');
const aiPreview = ref(null);
let aiCtrl = null;

// 每次打开弹窗：提示词与上次生成结果一律置空，避免残留内容被误插入新目标（新建/切换目标时均为全新会话）
watch(
  () => props.modelValue,
  (open) => {
    if (!open) return;
    aiPrompt.value = '';
    aiError.value = '';
    aiText.value = '';
    aiReasoning.value = '';
    aiPreviewHtml.value = '';
  },
);

function beforeClose(done) {
  if (aiGenerating.value) {
    ElMessageBox.confirm('AI 正在写作中，确定停止并关闭？', '提示', { type: 'warning' })
      .then(() => {
        stopGenerate();
        done();
      })
      .catch(() => {});
  } else {
    done();
  }
}

function close() {
  emit('update:modelValue', false);
}

function stopGenerate() {
  if (aiCtrl) {
    aiCtrl.abort();
    aiCtrl = null;
  }
  aiGenerating.value = false;
  aiSubmitting.value = false;
}

// 开始流式生成（SSE）：复用 api.js 的通用 sseRequest（/ai/chat_stream，与系统富文本 AI 写作同接口）
function startGenerate() {
  const prompt = aiPrompt.value.trim();
  if (!prompt) {
    ElMessage.error('请输入写作提示词');
    return;
  }
  aiError.value = '';
  aiText.value = '';
  aiReasoning.value = '';
  aiPreviewHtml.value = '';
  aiGenerating.value = true;
  aiSubmitting.value = true;
  const ctrl = new AbortController();
  aiCtrl = ctrl;
  // 技能元信息两种模式都随请求携带；rewrite 模式原文作为上下文与修改意见一起下发，write 模式仅下发提示词
  const systemPrompt = isRewrite.value
    ? `本次优化目标：${props.targetLabel}。\n` + REWRITE_SYSTEM_PROMPT
    : `本次撰写目标：${props.targetLabel}。\n` + DEFAULT_SYSTEM_PROMPT;
  const contextLine = props.contextText.trim() ? `【技能信息】\n${props.contextText.trim()}\n\n` : '';
  const bodyPrompt = isRewrite.value
    ? `请按以下修改意见改写原文：\n${contextLine}【修改意见】${prompt}\n\n【原文开始】\n${props.sourceText.trim()}\n【原文结束】`
    : `${contextLine}【写作要求】\n${prompt}`;
  const prompts = systemPrompt
    .split(/\r?\n/)
    .map((line) => line.trim())
    .filter(Boolean);
  sseRequest('ai/chat_stream', { prompt: bodyPrompt, prompts }, handleEvent, { signal: ctrl.signal })
    .catch((err) => {
      if (err && err.name === 'AbortError') return;
      aiError.value = (err && err.message) || 'AI 生成失败';
      ElMessage.error(aiError.value);
    })
    .finally(() => {
      aiGenerating.value = false;
      aiSubmitting.value = false;
      aiCtrl = null;
    });
}

// SSE 事件处理：payload 已由 sseRequest 反序列化（delta.content / reasoning.content / done / error.msg）
function handleEvent(payload) {
  if (payload.event === 'delta' && payload.content) {
    aiText.value += payload.content;
    aiPreviewHtml.value = mdToHtml(aiText.value);
    scrollBottom();
  } else if (payload.event === 'reasoning' && payload.content) {
    // 思维链增量：仅累计用于状态展示，避免推理期间长时间无反馈被误认为卡死
    aiReasoning.value += payload.content;
  } else if (payload.event === 'done') {
    // 空结果兜底：推理模型可能把输出全部耗在思维链（reasoning）或因额度/异常未产出正文
    if (!aiText.value.trim()) {
      aiError.value = aiReasoning.value.trim()
        ? '模型仅输出思考过程，未返回正文（可能输出额度被思考耗尽），请重试或精简改写要求'
        : 'AI 未返回任何内容，请重试';
      ElMessage.error(aiError.value);
      return;
    }
    // 生成完成即清洗：丢弃首行一级标题之前的引导语/寒暄（模型偶发违反指令的兜底）
    aiText.value = sanitizeMd(aiText.value);
    aiPreviewHtml.value = mdToHtml(aiText.value);
    ElMessage.success('AI 写作完成，可插入编辑区');
  } else if (payload.event === 'error') {
    aiError.value = payload.msg || 'AI 生成失败';
    ElMessage.error(aiError.value);
  }
}

// 清洗生成结果：格式约定正文首行即一级标题（# ）。标题在行首时剥离其前的多行引导语/寒暄；
// 引导语与标题同行（如「现在开始输出改写后的正文：# 标题」）时一并剥离短引导语；无一级标题则原样返回
function sanitizeMd(text) {
  const trimmed = text.trim();
  const atLineStart = trimmed.match(/^#[ \t]/m);
  if (atLineStart) return trimmed.slice(atLineStart.index).trim();
  // 同行兜底：仅当首个一级标题前是无换行、无 # 的短引导语（≤60 字）时才视为寒暄剥离，防止误伤含 # 的正文
  const inline = trimmed.match(/^[^\n#]{1,60}?#[ \t]/);
  if (inline) return trimmed.slice(inline.index).trim();
  return trimmed;
}

function scrollBottom() {
  nextTick(() => {
    const el = aiPreview.value;
    if (el) el.scrollTop = el.scrollHeight;
  });
}

// 插入：回传纯 Markdown 文本（先清洗再回传，不经过 HTML 转换，保证源码格式无损）
function insert() {
  const text = sanitizeMd(aiText.value).trim();
  if (!text) {
    ElMessage.error('暂无可插入的内容');
    return;
  }
  emit('insert', text);
  emit('update:modelValue', false);
  ElMessage.success(isRewrite.value ? '已替换编辑区内容' : '已插入编辑区');
}

// 简易 Markdown 转 HTML（仅用于弹窗内预览）
function mdToHtml(md) {
  const escapeHtml = (s) => s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  const inline = (s) =>
    escapeHtml(s)
      .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
      .replace(/\*(.+?)\*/g, '<em>$1</em>')
      .replace(/`([^`]+?)`/g, '<code>$1</code>');
  let html = '';
  let listTag = '';
  const closeList = () => {
    if (listTag) {
      html += '</' + listTag + '>';
      listTag = '';
    }
  };
  md.split(/\r?\n/).forEach((rawLine) => {
    const line = rawLine.trim();
    if (!line || /^(```|~~~)/.test(line)) {
      closeList();
      return;
    }
    if (/^(-{3,}|\*{3,})$/.test(line)) {
      closeList();
      html += '<hr/>';
    } else if (/^#{1,6}\s+/.test(line)) {
      closeList();
      const level = Math.min(line.match(/^#+/)[0].length + 1, 6);
      html += '<h' + level + '>' + inline(line.replace(/^#{1,6}\s+/, '')) + '</h' + level + '>';
    } else if (/^[-*+]\s+/.test(line)) {
      if (listTag !== 'ul') {
        closeList();
        html += '<ul>';
        listTag = 'ul';
      }
      html += '<li>' + inline(line.replace(/^[-*+]\s+/, '')) + '</li>';
    } else if (/^\d+\.\s+/.test(line)) {
      if (listTag !== 'ol') {
        closeList();
        html += '<ol>';
        listTag = 'ol';
      }
      html += '<li>' + inline(line.replace(/^\d+\.\s+/, '')) + '</li>';
    } else if (/^>\s?/.test(line)) {
      closeList();
      html += '<blockquote><p>' + inline(line.replace(/^>\s?/, '')) + '</p></blockquote>';
    } else {
      closeList();
      html += '<p>' + inline(line) + '</p>';
    }
  });
  closeList();
  return html || '<p></p>';
}

onBeforeUnmount(stopGenerate);
</script>

<style lang="scss" scoped>
.ai-status {
  margin-top: 12px;
  font-size: 12px;
  .ai-status-text {
    color: var(--prev-color-primary);
  }
  .ai-status-error {
    color: #f56c6c;
  }
  .ai-status-tip {
    color: #999;
  }
}
.ai-preview {
  margin-top: 12px;
  max-height: 400px;
  overflow-y: auto;
  padding: 12px 16px;
  border: 1px solid var(--prev-border-color-base);
  border-radius: 4px;
  background: var(--prev-bg-color);
  font-size: 14px;
  line-height: 1.7;
  :deep() {
    h2,
    h3,
    h4,
    h5,
    h6 {
      margin: 10px 0 6px;
    }
    p {
      margin: 6px 0;
    }
    ul,
    ol {
      margin: 6px 0;
      padding-left: 20px;
    }
    blockquote {
      margin: 6px 0;
      padding: 4px 10px;
      border-left: 3px solid #ccc;
      color: #666;
    }
    code {
      background: var(--prev-bg-color);
      padding: 1px 4px;
      border-radius: 3px;
    }
    hr {
      border: none;
      border-top: 1px solid #ddd;
    }
  }
}
</style>
