<template>
  <el-descriptions :column="1" border size="large" class="schema-detail">
    <el-descriptions-item v-for="(field, index) in fields" :key="index" :label="field.label" :label-width="120">
      <!-- JSON 格式化展示（右上角一键复制） -->
      <div v-if="field.type === 'json'" class="schema-detail-json-wrap">
        <el-button class="schema-detail-json__copy" link type="primary" @click="onCopy(field.value)">复制</el-button>
        <pre class="schema-detail-json">{{ field.value }}</pre>
      </div>
      <!-- 标签展示 -->
      <el-tag v-else-if="field.type === 'tag'" :type="tagTheme(field)" disable-transitions>{{
        tagLabel(field)
      }}</el-tag>
      <!-- 时间展示 -->
      <span v-else-if="field.type === 'datetime'">{{ formatTime(field.value, true) }}</span>
      <span v-else-if="field.type === 'date'">{{ formatTime(field.value, false) }}</span>
      <!-- 默认文本 -->
      <span v-else>{{
        field.value === '' || field.value === null || field.value === undefined ? '-' : field.value
      }}</span>
    </el-descriptions-item>
  </el-descriptions>
</template>

<script setup>
/**
 * 通用页面详情面板（独立组件）
 *
 * 按 fields 声明渲染描述列表，支持 text/datetime/date/tag/json 五种封闭类型。
 */
import { ElButton, ElDescriptions, ElDescriptionsItem, ElMessage, ElTag } from 'element-plus';
import { formatTime } from '../types';

defineOptions({ name: 'SchemaDetail' });

defineProps({
  /** 详情字段 [{label, value, type?, map?, theme?}] */
  fields: {
    type: Array,
    default: () => [],
  },
});

/**
 * @description 复制 JSON/代码内容到剪贴板
 *
 * 优先 Clipboard API（需安全上下文），失败（如 http 部署、无焦点环境）时
 * 回退 execCommand 方案，保证各类部署形态均可复制。
 * @param {string} text 复制内容
 */
async function onCopy(text) {
  const content = String(text ?? '');
  if (await copyText(content)) {
    ElMessage.success('已复制到剪贴板');
  } else {
    ElMessage.error('复制失败，请手动选择复制');
  }
}

/**
 * @description 执行复制并返回是否成功
 * @param {string} content 复制内容
 * @returns {Promise<boolean>}
 */
async function copyText(content) {
  if (navigator.clipboard && window.isSecureContext) {
    try {
      await navigator.clipboard.writeText(content);
      return true;
    } catch {
      // 降级走 execCommand
    }
  }
  const textarea = document.createElement('textarea');
  textarea.value = content;
  textarea.style.position = 'fixed';
  textarea.style.opacity = '0';
  document.body.appendChild(textarea);
  textarea.select();
  let success = false;
  try {
    success = document.execCommand('copy');
  } catch {
    success = false;
  }
  document.body.removeChild(textarea);
  return success;
}

/** 标签文案（map 优先，theme 兜底） */
function tagLabel(field) {
  const conf = (field.map || {})[String(field.value ?? '')] || {};
  return conf.label ?? field.theme ?? String(field.value ?? '-');
}

/** 标签主题色 */
function tagTheme(field) {
  const conf = (field.map || {})[String(field.value ?? '')] || {};
  return conf.theme || field.theme || 'info';
}
</script>

<style lang="scss" scoped>
.schema-detail-json-wrap {
  position: relative;
  &__copy {
    position: absolute;
    top: 2px;
    right: 4px;
    z-index: 1;
  }
}
.schema-detail-json {
  margin: 0;
  padding: 8px 12px;
  max-height: 320px;
  overflow: auto;
  background: #f5f7fa;
  border-radius: 4px;
  font-size: 12px;
  line-height: 1.6;
  white-space: pre-wrap;
  word-break: break-all;
}
</style>
