<template>
  <el-dialog v-model="modal" width="720px" title="AI 写作" :close-on-click-modal="false" :before-close="beforeClose">
    <el-input
      v-model="prompt"
      type="textarea"
      :rows="4"
      maxlength="2000"
      show-word-limit
      placeholder="请输入写作提示词，例如：写一篇关于秋季新品发布会的推广文章，介绍新品亮点与上市优惠，800字左右"
      :disabled="generating"
    ></el-input>
    <!-- 系统提示词（可自定义，默认保留内置提示词；可通过 ai-show-system-prompt 配置显隐） -->
    <template v-if="aiShowSystemPrompt">
      <div class="ai-prompt-head">
        <span class="ai-prompt-label">系统提示词（决定 AI 的角色与输出要求，每行一条）</span>
        <el-button type="text" class="ai-reset-btn" v-db-click @click="resetSystemPrompt">恢复默认</el-button>
      </div>
      <el-input
        v-model="systemPrompt"
        type="textarea"
        :rows="3"
        placeholder="定义 AI 的角色与输出要求，每行一条"
        :disabled="generating"
      ></el-input>
    </template>
    <div class="ai-status">
      <span v-if="generating" class="ai-status-text"> AI 正在写作，已生成 {{ text.length }} 字… </span>
      <span v-else-if="error" class="ai-status-error">{{ error }}</span>
      <span v-else-if="text" class="ai-status-tip">生成完成，点击「插入编辑器」写入文章内容</span>
      <span v-else class="ai-status-tip">点击「开始生成」，AI 内容将在下方实时输出</span>
    </div>
    <!-- 流式输出预览区 -->
    <div v-if="text || generating" ref="previewRef" class="ai-preview" v-html="previewHtml"></div>
    <template #footer
      ><div>
        <template v-if="!generating">
          <el-button v-db-click @click="modal = false">{{ text ? '关闭' : '取消' }}</el-button>
          <el-button v-if="text" v-db-click @click="startGenerate">重新生成</el-button>
          <el-button v-if="text" type="primary" v-db-click @click="insertToEditor">插入编辑器</el-button>
          <el-button v-else type="primary" :loading="submitting" v-db-click @click="startGenerate">开始生成</el-button>
        </template>
        <el-button v-else type="danger" v-db-click @click="stopGenerate">停止生成</el-button>
      </div></template
    >
  </el-dialog>
</template>

<script setup>
/**
 * AI 写作弹窗（富文本编辑器公共组件）
 *
 * 独立封装 AI 写作：提示词/系统提示词输入、SSE 流式生成（/ai/chat_stream）、
 * Markdown 预览与插入回调；供系统 wangEditor 与 SchemaPage 的 SchemaEditor 复用。
 * 父组件监听编辑器 AI 菜单的 bus 事件（AiWrite，需自行判断编辑器实例归属）后调用
 * open()；「插入编辑器」动作经 insert 事件（参数为 Markdown 转换后的 HTML）交由
 * 父组件写入各自的编辑器实例。
 */
import { nextTick, onBeforeUnmount, ref } from 'vue';
import { ElMessage, ElMessageBox } from 'element-plus';
import { getCookies } from '@/libs/util';
import Setting from '@/setting';

defineOptions({ name: 'AiModal' });

const props = defineProps({
  // AI 写作默认问题（打开弹窗时输入框为空则填充，用户可修改），不传时使用内置默认
  aiDefaultPrompt: {
    type: String,
    default: '',
  },
  // AI 写作默认系统提示词，不传时使用内置默认
  aiDefaultSystemPrompt: {
    type: String,
    default: '',
  },
  // 是否显示系统提示词编辑区（默认隐藏，隐藏时仍按默认值生效；页面传 true 显示）
  aiShowSystemPrompt: {
    type: Boolean,
    default: false,
  },
});

// 插入编辑器（参数：Markdown 转换后的 HTML）
const emit = defineEmits(['insert']);

// AI 写作默认系统提示词（生成时按行拆分后作为 prompts 传给后端）
const AI_DEFAULT_SYSTEM_PROMPT =
  '你是一名专业的新媒体内容编辑，擅长撰写图文文章。请根据用户要求直接撰写文章正文。\n' +
  '输出要求：使用 Markdown 格式，章节标题用 ## 或 ###，重点内容用 **加粗**，条目用 - 列表；直接输出正文，不要输出任何解释说明或与正文无关的内容。';

// AI 写作输入框默认提示词（打开弹窗时输入框为空则填充，用户可修改）
const AI_DEFAULT_PROMPT = '写一篇产品推广文章，介绍产品亮点、使用场景与适用人群，结构清晰，800字左右。';

const modal = ref(false);
const prompt = ref('');
const systemPrompt = ref('');
const generating = ref(false);
const submitting = ref(false);
const error = ref('');
const text = ref('');
const previewHtml = ref('');
const previewRef = ref(null);
let ctrl = null;
// 上次填充进输入框的默认提示词（用于打开时判断输入框内容是否被用户修改过；
// 页面动态切换默认提示词（如协议页切换协议）时，未被修改的旧默认值可跟随刷新）
let lastAppliedPrompt = '';
let lastAppliedSystemPrompt = '';

/**
 * 打开弹窗（输入框为空或仍为上次默认值时填充当前默认提示词；用户已修改的内容保留）
 * @returns {void}
 */
function open() {
  error.value = '';
  const defPrompt = props.aiDefaultPrompt || AI_DEFAULT_PROMPT;
  const defSystemPrompt = props.aiDefaultSystemPrompt || AI_DEFAULT_SYSTEM_PROMPT;
  if (!prompt.value.trim() || prompt.value === lastAppliedPrompt) prompt.value = defPrompt;
  if (!systemPrompt.value.trim() || systemPrompt.value === lastAppliedSystemPrompt) {
    systemPrompt.value = defSystemPrompt;
  }
  lastAppliedPrompt = prompt.value;
  lastAppliedSystemPrompt = systemPrompt.value;
  modal.value = true;
}

// 恢复默认系统提示词（页面配置优先，其次组件内置默认）
function resetSystemPrompt() {
  systemPrompt.value = props.aiDefaultSystemPrompt || AI_DEFAULT_SYSTEM_PROMPT;
  lastAppliedSystemPrompt = systemPrompt.value;
}
// 关闭弹窗前确认（生成中先停止）
function beforeClose(done) {
  if (generating.value) {
    ElMessageBox.confirm('AI 正在写作中，确定停止并关闭？', '提示', {
      type: 'warning',
    })
      .then(() => {
        stopGenerate();
        done();
      })
      .catch(() => {});
  } else {
    done();
  }
}
// 停止 AI 生成
function stopGenerate() {
  if (ctrl) {
    ctrl.abort();
    ctrl = null;
  }
  generating.value = false;
  submitting.value = false;
}
// 开始 AI 流式生成（SSE，内容实时输出到弹窗预览区）
function startGenerate() {
  const value = prompt.value.trim();
  if (!value) {
    ElMessage.error('请输入写作提示词');
    return;
  }

  error.value = '';
  text.value = '';
  previewHtml.value = '';
  generating.value = true;
  submitting.value = true;
  const newCtrl = new AbortController();
  ctrl = newCtrl;

  fetch(Setting.apiBaseURL + '/ai/chat_stream', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authori-zation': 'Bearer ' + getCookies('token'),
    },
    body: JSON.stringify({
      prompt: value,
      // 系统提示词按行拆分（空行过滤），为空时后端使用内置默认
      prompts: systemPrompt.value
        .split(/\r?\n/)
        .map((line) => line.trim())
        .filter(Boolean),
    }),
    signal: newCtrl.signal,
  })
    .then((response) => {
      submitting.value = false;
      if (!response.ok) {
        if (response.status === 401) throw new Error('登录已失效，请重新登录');
        if (response.status === 403) throw new Error('暂无操作权限');
        throw new Error('请求失败（HTTP ' + response.status + '）');
      }
      if (!response.body) throw new Error('当前浏览器不支持流式响应');
      return readStream(response.body);
    })
    .catch((err) => {
      if (err && err.name === 'AbortError') {
        // 主动停止，不做错误提示
      } else {
        error.value = (err && err.message) || 'AI 生成失败';
        ElMessage.error(error.value);
      }
    })
    .finally(() => {
      generating.value = false;
      submitting.value = false;
      ctrl = null;
    });
}
// 逐块读取 SSE 流并分发事件
async function readStream(body) {
  const reader = body.getReader();
  const decoder = new TextDecoder('utf-8');
  let buffer = '';
  while (true) {
    const { done, value } = await reader.read();
    if (done) break;
    buffer += decoder.decode(value, { stream: true });
    let idx;
    // SSE 事件以空行（\n\n）分隔
    while ((idx = buffer.indexOf('\n\n')) !== -1) {
      const rawEvent = buffer.slice(0, idx);
      buffer = buffer.slice(idx + 2);
      handleEvent(rawEvent);
    }
  }
}
// 处理单个 SSE 事件（data: {...}）
function handleEvent(rawEvent) {
  const dataLine = rawEvent.split('\n').find((line) => line.startsWith('data:'));
  if (!dataLine) return;
  let payload;
  try {
    payload = JSON.parse(dataLine.slice(5).trim());
  } catch (e) {
    return;
  }
  if (payload.event === 'delta' && payload.content) {
    text.value += payload.content;
    // 增量重绘弹窗预览区（Markdown 转 HTML）
    previewHtml.value = mdToHtml(text.value);
    scrollPreviewBottom();
  } else if (payload.event === 'done') {
    ElMessage.success('AI 写作完成，可插入编辑器');
  } else if (payload.event === 'error') {
    error.value = payload.msg || 'AI 生成失败';
    ElMessage.error(error.value);
  }
}
// 预览区滚动到底部（跟随生成进度）
function scrollPreviewBottom() {
  nextTick(() => {
    const el = previewRef.value;
    if (el) el.scrollTop = el.scrollHeight;
  });
}
// 将 AI 生成内容交由父组件插入编辑器（emit 后关闭弹窗）
function insertToEditor() {
  if (!text.value || !previewHtml.value) {
    ElMessage.error('暂无可插入的内容');
    return;
  }
  emit('insert', previewHtml.value);
  modal.value = false;
  ElMessage.success('已插入编辑器');
}
// 简易 Markdown 转 HTML（标题/有序无序列表/粗体/斜体/行内代码/引用/分割线）
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
    // 空行与 ``` 代码围栏行不参与渲染
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

// 组件卸载时中止未完成的生成请求
onBeforeUnmount(stopGenerate);

defineExpose({ open });
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
.ai-prompt-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 12px;
  .ai-prompt-label {
    font-size: 12px;
    color: #999;
  }
  .ai-reset-btn {
    padding: 0;
    font-size: 12px;
  }
}
/* 流式输出预览区（v-html 内容需 :deep(穿透 scoped） */
.ai-preview {
  margin-top: 12px;
  max-height: 400px;
  overflow-y: auto;
  padding: 12px 16px;
  border: 1px solid #e5e5e5;
  border-radius: 4px;
  background: #fafafa;
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
    hr {
      border: none;
      border-top: 1px solid #ddd;
    }
  }
}
</style>
