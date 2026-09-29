<template>
  <el-date-picker
    :model-value="modelValue"
    type="daterange"
    :shortcuts="timeOptions.shortcuts"
    range-separator="至"
    start-placeholder="开始日期"
    end-placeholder="结束日期"
    placement="bottom-start"
    :popper-options="mergedPopperOptions"
    :style="width ? { width, flexGrow: 0 } : undefined"
    @update:model-value="onUpdateModelValue"
    @change="onChange"
    v-bind="restAttrs"
  />
</template>

<script setup>
/**
 * 日期范围选择器（全局封装）
 * @description 时间搜索统一入口：内置系统快捷项（今天/昨天/本月/上月/最近N天/本年/去年，来自 libs/timeOptions），
 * 默认宽度 220px（弹窗等非筛选场景）；筛选行消费方一律显式传 width 对齐同行控件（250px 基准），
 * 并强制 flex-grow:0——EP 的 .el-input__wrapper 自带 flex-grow:1，
 * 不压制会在 flex 搜索行内被拉伸占满。
 * 弹层定位：placement 默认 bottom-start（左对齐），并开启 preventOverflow 的 altAxis——
 * 视口右侧放不下时弹层被推回、右缘贴齐视口（等效右对齐），左侧溢出同理兜底；
 * 调用方传 placement / popper-options 可覆盖默认行为（popper-options 的 modifiers 会与默认合并）。
 * format、value-format、clearable 等其余属性经 $attrs 透传给 el-date-picker。
 */
import { computed, useAttrs } from 'vue';
import timeOptions from '@/libs/timeOptions';

defineOptions({ name: 'DateRangePicker', inheritAttrs: false });

defineProps({
  modelValue: { type: Array, default: () => [] },
  /** 选择器宽度，传空串则不设置（由外部样式控制）；筛选行请显式传 width 对齐同行控件 */
  width: { type: String, default: '220px' },
});

const emit = defineEmits(['update:modelValue', 'change']);

// 弹层水平防溢出：popper.js preventOverflow 默认只管主轴（垂直），altAxis 打开横向推挤
const DEFAULT_POPPER_OPTIONS = {
  modifiers: [{ name: 'preventOverflow', options: { altAxis: true, padding: 8 } }],
};

const attrs = useAttrs();

// attrs 保留调用方原始键名（camelCase / kebab-case 均有可能），两种写法都读取
const callerPopperOptions = computed(() => attrs.popperOptions || attrs['popper-options']);

// 调用方传入的 popper-options 与默认合并（modifiers 追加，同名 modifier 由 popper.js 按后者覆盖选项）
const mergedPopperOptions = computed(() => {
  const extra = callerPopperOptions.value;
  if (!extra) return DEFAULT_POPPER_OPTIONS;
  return {
    ...DEFAULT_POPPER_OPTIONS,
    ...extra,
    modifiers: [...DEFAULT_POPPER_OPTIONS.modifiers, ...(extra.modifiers || [])],
  };
});

// popperOptions 已在 mergedPopperOptions 中合并处理，从透传 attrs 中剔除避免覆盖组件默认
const restAttrs = computed(() => {
  const { popperOptions, 'popper-options': _popperOptions, ...rest } = attrs;
  return rest;
});

function onUpdateModelValue(value) {
  emit('update:modelValue', value);
}

function onChange(value) {
  emit('change', value);
}
</script>
