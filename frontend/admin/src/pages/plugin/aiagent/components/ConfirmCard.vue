<template>
  <div class="confirm-row">
    <div :class="['confirm-card', message.status]">
      <div class="confirm-head">
        <i class="el-icon-warning-outline" />
        <span>AI 请求执行操作</span>
        <span v-if="message.status === 'approved'" class="confirm-state ok">已执行</span>
        <span v-else-if="message.status === 'rejected'" class="confirm-state">已取消</span>
        <span v-else-if="message.status === 'expired'" class="confirm-state">已过期</span>
      </div>
      <div class="confirm-tool">{{ cardTitle }}</div>
      <div v-if="cardParamList.length" class="confirm-args">
        <div v-for="(item, i) in cardParamList" :key="i" class="arg-row">
          <span v-if="item.key" class="arg-key">{{ item.key }}：</span><span class="arg-value">{{ item.value }}</span>
          <a v-if="item.long" class="arg-toggle" @click="toggleExpand(i)">{{ expanded.has(i) ? '收起' : '展开' }}</a>
        </div>
      </div>
      <div v-if="htmlContent" class="confirm-preview">
        <div class="preview-head" @click="previewOpen = !previewOpen">
          <span>内容预览</span>
          <i :class="previewOpen ? 'el-icon-arrow-up' : 'el-icon-arrow-down'" />
        </div>
        <iframe v-show="previewOpen" class="preview-frame" sandbox="" :srcdoc="htmlContent" frameborder="0" />
      </div>
      <div v-if="message.status === 'pending'" class="confirm-actions">
        <button class="confirm-btn cancel" type="button" @click="emit('resolve', message, 'reject')">取消</button>
        <button
          class="confirm-btn ok"
          type="button"
          :disabled="chatting"
          v-db-click
          @click="emit('resolve', message, 'approve')"
        >
          确认执行
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, reactive, ref } from 'vue';

defineOptions({ name: 'AiAgentConfirmCard' });

/** 参数值展示长度上限：超过则截断，提供展开/收起 */
const VALUE_LIMIT = 100;

const props = defineProps({
  /** confirm 消息对象（display 承载模型提供的中文说明，arguments 为参数快照） */
  message: { type: Object, required: true },
  /** 是否有流式请求进行中（进行中禁止操作） */
  chatting: { type: Boolean, default: false },
});

/** resolve(message, action)：action = approve | reject */
const emit = defineEmits(['resolve']);

/** 卡片标题：优先模型提供的中文说明，降级为工具名 */
const cardTitle = computed(() => (props.message.display && props.message.display.title) || props.message.toolName);

/** 卡片参数：优先模型提供的中文参数说明，降级为原始参数 */
const cardParams = computed(() => {
  const params = props.message.display && props.message.display.params;
  return params && Object.keys(params).length ? params : props.message.arguments;
});

/** 已展开长值的下标集合 */
const expanded = reactive(new Set());

/** 切换长值展开/收起 */
function toggleExpand(i) {
  if (expanded.has(i)) {
    expanded.delete(i);
  } else {
    expanded.add(i);
  }
}

/** 内容预览：arguments.content 为 HTML（富文本正文类工具，如发送邮件）时提供沙箱渲染预览 */
const htmlContent = computed(() => {
  const args = props.message.arguments || {};
  const content = typeof args.content === 'string' ? args.content.trim() : '';
  return content.startsWith('<') ? content : '';
});

/** 预览默认展开：确认执行前用户最关心的就是内容长什么样 */
const previewOpen = ref(true);

/**
 * 参数条目归一化：约定形态为 {参数名: 取值} 对象，每条渲染为「参数名：取值」单行；
 * 模型偶发把键值平铺成数组（截图错位换行的根源），此时按序两两配对兜底；
 * 超长取值截断展示，展开后查看全文；已提供渲染预览的 HTML 正文不再在参数区重复展示原文
 */
const cardParamList = computed(() => {
  const raw = cardParams.value;
  const entries = Array.isArray(raw)
    ? Array.from({ length: Math.ceil(raw.length / 2) }, (_, i) => [raw[i * 2], raw[i * 2 + 1]])
    : Object.entries(raw || {});
  return entries
    .map(([key, value], i) => {
      const text = formatValue(value);
      const long = text.length > VALUE_LIMIT;
      return {
        key: key === undefined || key === null ? '' : String(key),
        full: text,
        value: long && !expanded.has(i) ? text.slice(0, VALUE_LIMIT) + '…' : text,
        long,
      };
    })
    .filter((item) => item.key !== '' || item.full !== '')
    .filter((item) => htmlContent.value === '' || item.full.trim() !== htmlContent.value);
});

/** 参数值展示：对象序列化为 JSON */
function formatValue(value) {
  if (value === null || value === undefined) return '';
  if (typeof value === 'object') return JSON.stringify(value);
  return String(value);
}
</script>

<style scoped lang="scss">
.confirm-row {
  display: flex;
  justify-content: center;
  margin-bottom: 30px;
}
.confirm-card {
  width: 380px;
  padding: 14px 16px;
  color: var(--prev-color-text-regular);
  background: #fdf6ec;
  border: 1px solid #faecd8;
  border-radius: 12px;
  .confirm-head {
    color: #b88230;
  }
  &.approved {
    background: #f0f9eb;
    border-color: #e1f3d8;
    .confirm-head {
      color: #67c23a;
    }
  }
  &.rejected,
  &.expired {
    color: var(--prev-color-text-secondary);
    background: #f4f4f5;
    border-color: #e9e9eb;
    .confirm-head {
      color: var(--prev-color-text-secondary);
    }
    .confirm-tool,
    .confirm-args {
      color: var(--prev-color-text-secondary);
    }
    .confirm-preview {
      opacity: 0.6;
    }
  }
}
.confirm-head {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  font-weight: 600;
  .confirm-state {
    margin-left: auto;
    color: var(--prev-color-text-secondary);
    font-size: 12px;
    font-weight: 400;
    &.ok {
      color: #67c23a;
    }
  }
}
.confirm-tool {
  margin-top: 10px;
  color: var(--prev-color-text-regular);
  font-size: 13px;
  font-weight: 600;
  word-break: break-all;
}
.confirm-args {
  margin-top: 8px;
  font-size: 12px;
  .arg-row {
    padding: 4px 0;
    line-height: 1.5;
    word-break: break-all;
  }
  .arg-key {
    color: var(--prev-color-text-secondary);
  }
  .arg-toggle {
    margin-left: 4px;
    color: var(--prev-color-primary);
    cursor: pointer;
    white-space: nowrap;
  }
}
.confirm-preview {
  margin-top: 8px;
  .preview-head {
    display: flex;
    align-items: center;
    gap: 4px;
    color: var(--prev-color-text-secondary);
    font-size: 12px;
    cursor: pointer;
    user-select: none;
    &:hover {
      color: var(--prev-color-text-primary);
    }
  }
  .preview-frame {
    width: 100%;
    height: 240px;
    margin-top: 6px;
    background: #fff;
    border: 1px solid #faecd8;
    border-radius: 8px;
  }
}
.confirm-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  margin-top: 12px;
}
.confirm-btn {
  padding: 5px 16px;
  font-size: 12px;
  cursor: pointer;
  border-radius: 14px;
  border: 1px solid transparent;
  &.cancel {
    color: var(--prev-color-text-primary);
    background: var(--prev-bg-white);
    border-color: var(--prev-border-color-base);
    &:hover {
      color: var(--prev-color-text-secondary);
      border-color: #c0c4cc;
    }
  }
  &.ok {
    color: #fff;
    background: var(--prev-color-primary);
    &:hover:not(:disabled) {
      opacity: 0.85;
    }
    &:disabled {
      cursor: not-allowed;
      background: #a0cfff;
    }
  }
}
</style>
