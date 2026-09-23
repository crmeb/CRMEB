<template>
  <component :is="cellComponent" v-bind="cellProps" @change="onSwitchChange">
    <template v-if="showSlotText">{{ displayText }}</template>
  </component>
</template>

<script setup>
/**
 * 通用页面表格单元格（列类型分发渲染）
 *
 * 按 column.type 将行数据渲染为对应形态：text/number/amount/datetime/date/tag/image/switch/link/ellipsis。
 * 未知类型已在后端 Validator 降级为 text，此处 switch 兜底处理非受控场景。
 */
import { computed } from 'vue';
import { ElTag, ElImage, ElSwitch, ElLink } from 'element-plus';
import { formatTime, formatNumber } from '../types';

defineOptions({ name: 'SchemaTableCell' });

const props = defineProps({
  /** 列定义 {prop,label,type,map,on_value,off_value,...} */
  column: {
    type: Object,
    required: true,
  },
  /** 行数据 */
  row: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(['switch-change']);

/** 原始值 */
const rawValue = computed(() => props.row[props.column.prop]);

/** 各类型的渲染组件（null 表示纯文本，由 el-table-column 直接输出） */
const cellComponent = computed(() => {
  switch (props.column.type) {
    case 'tag':
      return ElTag;
    case 'image':
      // 无图片地址时降级为文本展示
      return rawValue.value ? ElImage : 'span';
    case 'switch':
      return ElSwitch;
    case 'link':
      return ElLink;
    default:
      return 'span';
  }
});

/** 组件属性 */
const cellProps = computed(() => {
  switch (props.column.type) {
    case 'tag': {
      const conf = (props.column.map || {})[String(rawValue.value ?? '')] || {};
      return { type: conf.theme || 'info', disableTransitions: true };
    }
    case 'image':
      return {
        src: rawValue.value,
        'preview-src-list': [rawValue.value],
        'preview-teleported': true,
        'hide-on-click-modal': true,
        fit: 'cover',
        style: 'width:36px;height:36px;border-radius:4px',
      };
    case 'switch':
      return {
        modelValue: rawValue.value,
        activeValue: props.column.on_value ?? 1,
        inactiveValue: props.column.off_value ?? 0,
      };
    case 'link':
      return { type: 'primary' };
    default:
      return {};
  }
});

/** 是否渲染文本 slot（image/switch 组件不携带文本内容） */
const showSlotText = computed(() => !['image', 'switch'].includes(props.column.type));

/** 文本展示值（text/number/amount/datetime/date/tag/link/ellipsis） */
const displayText = computed(() => {
  const value = rawValue.value;
  // tag 类型空字符串为合法映射键，优先走 map 匹配
  if (props.column.type === 'tag') {
    const conf = (props.column.map || {})[String(value ?? '')] || {};
    return conf.label ?? String(value ?? '-');
  }
  if (value === null || value === undefined || value === '') return '-';
  switch (props.column.type) {
    case 'number':
      return formatNumber(value);
    case 'amount':
      return `¥${Number(value).toFixed(2)}`;
    case 'datetime':
      return formatTime(value, true);
    case 'date':
      return formatTime(value, false);
    default:
      return String(value);
  }
});

/** 开关切换事件（受控组件模式，由父级发起请求后刷新列表） */
function onSwitchChange() {
  emit('switch-change', {
    column: props.column,
    row: props.row,
    value: rawValue.value === (props.column.on_value ?? 1) ? props.column.off_value ?? 0 : props.column.on_value ?? 1,
  });
}
</script>
