<template>
  <div class="confirm-row">
    <div :class="['confirm-card', 'ask', message.status]">
      <div class="confirm-head">
        <el-icon><ChatLineSquare /></el-icon>
        <span>AI 需要你确认</span>
        <span v-if="message.status === 'approved'" class="confirm-state ok">已回答</span>
        <span v-else-if="message.status === 'rejected'" class="confirm-state">已跳过</span>
        <span v-else-if="message.status === 'expired'" class="confirm-state">已过期</span>
      </div>
      <div class="ask-question">{{ form.question }}</div>
      <template v-if="form.type !== 'text'">
        <div class="ask-options">
          <div
            v-for="option in form.options"
            :key="option.value"
            :class="['ask-option', { selected: isSelected(option.value) }]"
            @click="toggleOption(option.value)"
          >
            <span :class="['ask-mark', form.type]">
              <i v-if="isSelected(option.value)" class="el-icon-check" />
            </span>
            <span class="ask-label">{{ option.label }}</span>
          </div>
          <div v-if="form.allowCustom" :class="['ask-option', { selected: useCustom }]" @click="toggleCustom">
            <span :class="['ask-mark', form.type]">
              <i v-if="useCustom" class="el-icon-check" />
            </span>
            <span class="ask-label">其他（自定义输入）</span>
          </div>
        </div>
        <div v-if="useCustom" class="ask-custom">
          <el-input
            v-model="customText"
            type="textarea"
            :rows="2"
            maxlength="500"
            resize="none"
            placeholder="请输入你的想法"
          />
        </div>
      </template>
      <div v-else class="ask-custom">
        <el-input v-model="customText" type="textarea" :rows="2" maxlength="500" resize="none" placeholder="请输入" />
      </div>
      <div v-if="message.status === 'pending'" class="confirm-actions">
        <button class="confirm-btn cancel" type="button" @click="emit('resolve', message, 'reject')">跳过</button>
        <button class="confirm-btn ok" type="button" :disabled="chatting || !answerReady" v-db-click @click="submit">
          提交
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';

defineOptions({ name: 'AiAgentAskCard' });

const props = defineProps({
  /** confirm 消息对象（arguments 承载问询参数快照，status 为确认状态） */
  message: { type: Object, required: true },
  /** 是否有流式请求进行中（进行中禁止提交） */
  chatting: { type: Boolean, default: false },
});

/** resolve(message, action, extra)：extra = { answers: 选中 value 列表, custom: 自定义输入 } */
const emit = defineEmits(['resolve']);

/** 问询参数规范化：options 兼容字符串与 {value,label} 两种形态 */
const form = computed(() => {
  const args = props.message.arguments || {};
  const options = (args.options || [])
    .map((option) => {
      if (option && typeof option === 'object') {
        return { value: String(option.value ?? ''), label: String(option.label ?? option.value ?? '') };
      }
      return { value: String(option), label: String(option) };
    })
    .filter((option) => option.value !== '');
  return {
    question: String(args.question || ''),
    type: ['single', 'multi', 'text'].includes(args.type) ? args.type : 'single',
    options,
    allowCustom: args.allow_custom !== false,
  };
});

/** 用户选中项与自定义输入（交互态组件内自持，不污染消息对象） */
const selected = ref([]);
const useCustom = ref(false);
const customText = ref('');

const isSelected = (value) => selected.value.includes(value);

/** 切换选项选中态：single 单选互斥（与自定义输入互斥），multi 多选 */
function toggleOption(value) {
  if (props.message.status !== 'pending') return;
  if (form.value.type === 'multi') {
    const index = selected.value.indexOf(value);
    if (index > -1) {
      selected.value.splice(index, 1);
    } else {
      selected.value.push(value);
    }
    return;
  }
  selected.value = selected.value.includes(value) ? [] : [value];
  useCustom.value = false;
}

/** 切换自定义输入：single 模式与选项互斥 */
function toggleCustom() {
  if (props.message.status !== 'pending') return;
  useCustom.value = !useCustom.value;
  if (useCustom.value && form.value.type === 'single') {
    selected.value = [];
  }
}

/** 答案是否可提交：至少选择一项或填写自定义内容 */
const answerReady = computed(() => {
  if (form.value.type === 'text') return customText.value.trim() !== '';
  const hasCustom = useCustom.value && customText.value.trim() !== '';
  return selected.value.length > 0 || hasCustom;
});

/** 提交问询答案 */
function submit() {
  if (!answerReady.value) return;
  const withCustom = form.value.type === 'text' || useCustom.value;
  emit('resolve', props.message, 'approve', {
    answers: selected.value.slice(),
    custom: withCustom ? customText.value.trim() : '',
  });
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
  background: var(--prev-color-primary-light-9);
  border: 1px solid #d9ecff;
  border-radius: 12px;
  .confirm-head {
    color: var(--prev-color-primary);
  }
  &.approved .confirm-head {
    color: #67c23a;
  }
  &.rejected .confirm-head,
  &.expired .confirm-head {
    color: var(--prev-color-text-secondary);
  }
  &.rejected,
  &.expired {
    color: var(--prev-color-text-secondary);
    background: var(--prev-bg-color);
    border-color: var(--prev-border-color-base);
    .ask-question,
    .ask-label {
      color: var(--prev-color-text-secondary);
    }
    .ask-option {
      cursor: default;
      border-color: var(--prev-border-color-base);
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
.ask-question {
  margin-top: 10px;
  color: var(--prev-color-text-regular);
  font-size: 14px;
  font-weight: 600;
  line-height: 1.6;
  word-break: break-word;
}
.ask-options {
  display: flex;
  margin-top: 10px;
  flex-direction: column;
  gap: 8px;
}
.ask-option {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  padding: 8px 12px;
  font-size: 13px;
  cursor: pointer;
  user-select: none;
  background: var(--prev-bg-white);
  border: 1px solid var(--prev-border-color-light);
  border-radius: 8px;
  transition: border-color 0.15s, background 0.15s;
  &:hover {
    border-color: var(--prev-color-primary);
  }
  &.selected {
    color: var(--prev-color-primary);
    background: var(--prev-color-primary-light-9);
    border-color: var(--prev-color-primary);
  }
}
.ask-mark {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  width: 16px;
  height: 16px;
  margin-top: 1px;
  color: #fff;
  font-size: 11px;
  background: var(--prev-bg-white);
  border: 1px solid var(--prev-border-color-hover);
  &.single {
    border-radius: 50%;
  }
  &.multi {
    border-radius: 4px;
  }
  .selected & {
    color: #fff;
    background: var(--prev-color-primary);
    border-color: var(--prev-color-primary);
  }
}
.ask-label {
  color: var(--prev-color-text-regular);
  line-height: 1.5;
  word-break: break-word;
}
.ask-custom {
  margin-top: 10px;
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
