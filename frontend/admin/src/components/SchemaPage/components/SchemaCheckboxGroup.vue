<template>
  <div class="schema-checkbox-group">
    <!-- 全选（部分勾选时呈半选态） -->
    <el-checkbox
      v-if="selectAll"
      class="schema-checkbox-group__all"
      :model-value="allChecked"
      :indeterminate="indeterminate"
      @change="toggleAll"
    >
      全选
    </el-checkbox>
    <el-checkbox-group
      class="schema-checkbox-group__grid"
      :style="gridStyle"
      :model-value="checkedValues"
      @update:model-value="onUpdate"
    >
      <!-- 选项：option.title 存在时悬停展示多行提示（如工具英文名 + 描述） -->
      <el-tooltip v-for="opt in options" :key="opt.value" :disabled="!tooltipText(opt)" placement="top" effect="dark">
        <template #content>
          <span class="schema-checkbox-group__tip">{{ tooltipText(opt) }}</span>
        </template>
        <el-checkbox :value="opt.value">{{ opt.label }}</el-checkbox>
      </el-tooltip>
    </el-checkbox-group>
  </div>
</template>

<script setup>
/**
 * 通用页面多列勾选组（form-create 自定义组件）
 *
 * 网格布局展示勾选项（columns 控制每行列数，默认 2 列），支持全选联动
 * （全选/清空/半选态），选项携带 title 时悬停展示多行提示。
 * 后端经 Form::checkboxGroup() 生成规则，值为选项 value 数组，
 * 校验等表单行为与普通字段一致。
 */
import { computed } from 'vue';
import { ElCheckbox, ElCheckboxGroup, ElTooltip } from 'element-plus';

defineOptions({ name: 'SchemaCheckboxGroup' });

const props = defineProps({
  /** 已勾选值数组（form-create field 值绑定） */
  modelValue: {
    type: Array,
    default: () => [],
  },
  /** 选项列表 [{label, value[, title]}]（后端规则 props 传入） */
  options: {
    type: Array,
    default: () => [],
  },
  /** 是否显示全选 */
  selectAll: {
    type: Boolean,
    default: true,
  },
  /** 每行列数 */
  columns: {
    type: Number,
    default: 2,
  },
  /** 字段名（后端规则 props 传入，仅保留标识用途） */
  field: {
    type: String,
    default: '',
  },
});

const emit = defineEmits(['update:modelValue']);

/** 选项值列表 */
const optionValues = computed(() => props.options.map((opt) => String(opt.value)));

/** 已勾选值 */
const checkedValues = computed(() => (props.modelValue || []).map(String));

/** 全选态：选项全部勾选 */
const allChecked = computed(
  () => optionValues.value.length > 0 && optionValues.value.every((value) => checkedValues.value.includes(value)),
);

/** 半选态：部分勾选 */
const indeterminate = computed(() => checkedValues.value.length > 0 && !allChecked.value);

/** 网格列数 */
const gridStyle = computed(() => ({
  display: 'grid',
  gridTemplateColumns: `repeat(${Math.max(1, props.columns)}, 1fr)`,
}));

/** 全选/清空联动 */
function toggleAll(checked) {
  emit('update:modelValue', checked ? [...optionValues.value] : []);
}

function onUpdate(values) {
  emit('update:modelValue', values || []);
}

/** 选项悬停提示（title 支持多行文本） */
function tooltipText(opt) {
  const text = String(opt?.title ?? '').trim();
  return text || '';
}
</script>

<style lang="scss" scoped>
.schema-checkbox-group {
  width: 100%;

  &__all {
    margin-bottom: 8px;
  }

  &__grid {
    width: 100%;

    :deep(.el-checkbox) {
      margin-right: 0;
      height: 26px;
    }
  }

  &__tip {
    display: inline-block;
    max-width: 400px;
    white-space: pre-line;
  }
}
</style>
