<template>
  <div class="schema-checkbox-group">
    <!-- 全选（部分勾选时呈半选态） -->
    <el-checkbox
      v-if="selectAll"
      class="schema-checkbox-group__all"
      :model-value="allChecked"
      :indeterminate="indeterminate"
      :disabled="disabled"
      @change="toggleAll"
    >
      全选
    </el-checkbox>
    <!-- 分组模式：选项携带 group 字段时按分组渲染，分组标题支持分组全选/清空 -->
    <template v-if="sections">
      <div v-for="section in sections" :key="section.name" class="schema-checkbox-group__section">
        <div class="schema-checkbox-group__head">
          <el-checkbox
            :model-value="sectionAllChecked(section)"
            :indeterminate="sectionIndeterminate(section)"
            :disabled="disabled"
            @change="(checked) => toggleSection(section, checked)"
          >
            {{ section.name }}
          </el-checkbox>
          <span class="schema-checkbox-group__num"
            >{{ sectionCheckedCount(section) }}/{{ section.options.length }}</span
          >
        </div>
        <el-checkbox-group
          class="schema-checkbox-group__grid"
          :style="gridStyle"
          :disabled="disabled"
          :model-value="checkedKeys"
          @update:model-value="onUpdate"
        >
          <!-- 选项：option.title 悬停多行提示；option.disabled 单项禁用；达 max 上限后未勾选项禁用 -->
          <el-tooltip
            v-for="opt in section.options"
            :key="keyOf(opt.value)"
            :disabled="!tooltipText(opt)"
            placement="top"
            effect="dark"
          >
            <template #content>
              <span class="schema-checkbox-group__tip">{{ tooltipText(opt) }}</span>
            </template>
            <el-checkbox :value="keyOf(opt.value)" :disabled="isOptionDisabled(opt)">{{ opt.label }}</el-checkbox>
          </el-tooltip>
        </el-checkbox-group>
      </div>
    </template>
    <!-- 平铺模式：选项无 group 字段时保持原有多列网格 -->
    <el-checkbox-group
      v-else
      class="schema-checkbox-group__grid"
      :style="gridStyle"
      :disabled="disabled"
      :model-value="checkedKeys"
      @update:model-value="onUpdate"
    >
      <el-tooltip
        v-for="opt in options"
        :key="keyOf(opt.value)"
        :disabled="!tooltipText(opt)"
        placement="top"
        effect="dark"
      >
        <template #content>
          <span class="schema-checkbox-group__tip">{{ tooltipText(opt) }}</span>
        </template>
        <el-checkbox :value="keyOf(opt.value)" :disabled="isOptionDisabled(opt)">{{ opt.label }}</el-checkbox>
      </el-tooltip>
    </el-checkbox-group>
  </div>
</template>

<script setup>
/**
 * 通用页面多列勾选组（form-create 自定义组件 · 契约组件）
 *
 * ⚠️ 跨系统契约，禁止为单一业务需求随手改写：后端 Form::checkboxGroup()
 * （backend/crmeb/utils/FormBuilder.php）生成 type=SchemaCheckboxGroup 规则，
 * SchemaForm 统一注册渲染，已编译插件 UMD 内含独立打包副本。
 * 能力扩展一律「新增可选 props + 默认行为与既有 props 语义不变」，
 * 并同步 FormBuilder 的 props 文档；红线见 .crmeb/rules/前端规范.md 第一节。
 *
 * Props 契约（均可由后端规则 props 下发）：
 * - modelValue 已勾选值数组，提交回传选项原始类型；
 * - options 选项 [{label, value[, group][, title][, disabled]}]：
 *   group 归并分组渲染（分组标题全选/清空 + 已选计数，组间保持选项给定顺序，
 *   后端负责排序）、title 悬停多行提示、disabled=true 单项禁用；
 * - selectAll 是否显示全选（默认 true）；columns 每行列数（默认 2）；
 * - disabled 整组禁用（默认 false）；
 * - max 最多可勾选数（默认 0 不限）：达上限后未勾选项禁用、批量全选按顺序
 *   截断，取消勾选不受限；禁用项不参与批量全选/清空（其勾选态不可被改写）。
 * 值统一按字符串 key 比对渲染、提交还原原始类型，规避后端数值/字符串类型不一致。
 */
import { computed } from 'vue';
import { ElCheckbox, ElCheckboxGroup, ElTooltip } from 'element-plus';

defineOptions({ name: 'SchemaCheckboxGroup' });

const props = defineProps({
  /** 已勾选值数组（form-create field 值绑定，提交回传原始类型） */
  modelValue: {
    type: Array,
    default: () => [],
  },
  /** 选项列表 [{label, value[, group][, title][, disabled]}]（后端规则 props 传入） */
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
  /** 整组禁用 */
  disabled: {
    type: Boolean,
    default: false,
  },
  /** 最多可勾选数，0 不限；达上限后未勾选项禁用，批量全选按顺序截断 */
  max: {
    type: Number,
    default: 0,
  },
});

const emit = defineEmits(['update:modelValue']);

/** 选项值字符串化 key（渲染比对统一用 key，规避数值/字符串类型不一致） */
function keyOf(value) {
  return String(value);
}

/** key → 原始 value 映射（提交时还原原始类型） */
const keyOrigin = computed(() => {
  const map = new Map();
  for (const opt of props.options) map.set(keyOf(opt.value), opt.value);
  return map;
});

/** 已勾选 key 数组 */
const checkedKeys = computed(() => (props.modelValue || []).map(keyOf));

/** 已勾选 key 集合 */
const checkedSet = computed(() => new Set(checkedKeys.value));

/** 全部选项 key（按选项顺序） */
const allKeys = computed(() => props.options.map((opt) => keyOf(opt.value)));

/** 可交互选项 key（仅剔除单项 disabled，不掺入 max 派生态，保证批量清空可达） */
const enabledKeys = computed(() =>
  props.options.filter((opt) => props.disabled !== true && opt?.disabled !== true).map((opt) => keyOf(opt.value)),
);

/** 单项是否禁用：整组禁用 / 单项 disabled / 达 max 上限且未勾选 */
function isOptionDisabled(opt) {
  if (props.disabled || opt?.disabled === true) return true;
  return props.max > 0 && !checkedSet.value.has(keyOf(opt.value)) && checkedKeys.value.length >= props.max;
}

/** 全选态：选项全部勾选 */
const allChecked = computed(() => allKeys.value.length > 0 && allKeys.value.every((key) => checkedSet.value.has(key)));

/** 半选态：部分勾选 */
const indeterminate = computed(() => checkedKeys.value.length > 0 && !allChecked.value);

/** 网格列数 */
const gridStyle = computed(() => ({
  display: 'grid',
  gridTemplateColumns: `repeat(${Math.max(1, props.columns)}, 1fr)`,
}));

/** 分组视图：任一选项携带 group 时按 group 归并（保持选项给定顺序），否则 null 走平铺 */
const sections = computed(() => {
  if (!props.options.some((opt) => String(opt?.group ?? '') !== '')) return null;
  const map = new Map();
  for (const opt of props.options) {
    const name = String(opt?.group ?? '') || '其他';
    if (!map.has(name)) map.set(name, { name, options: [] });
    map.get(name).options.push(opt);
  }
  return [...map.values()];
});

/** 分组内已勾选数 */
function sectionCheckedCount(section) {
  const keys = new Set(section.options.map((opt) => keyOf(opt.value)));
  return checkedKeys.value.filter((key) => keys.has(key)).length;
}

/** 分组全选态：组内选项全部勾选 */
function sectionAllChecked(section) {
  return section.options.length > 0 && sectionCheckedCount(section) === section.options.length;
}

/** 分组半选态：组内部分勾选 */
function sectionIndeterminate(section) {
  const count = sectionCheckedCount(section);
  return count > 0 && count < section.options.length;
}

/**
 * 统一提交入口：去重后既有勾选优先保留，新增按顺序受 max 上限约束
 * （达上限的新增忽略，取消勾选不受限），提交还原选项原始类型
 */
function commitKeys(keys) {
  if (props.disabled) return;
  const proposed = [...new Set(keys)];
  const kept = proposed.filter((key) => checkedSet.value.has(key));
  const added = proposed.filter((key) => !checkedSet.value.has(key));
  const room = props.max > 0 ? Math.max(0, props.max - kept.length) : added.length;
  const next = kept.concat(added.slice(0, room));
  emit(
    'update:modelValue',
    next.map((key) => keyOrigin.value.get(key)).filter((value) => value !== undefined),
  );
}

/** 全选/清空联动（仅操作可交互项：禁用项的勾选态不被批量操作改写） */
function toggleAll(checked) {
  if (checked) {
    commitKeys([...checkedKeys.value, ...enabledKeys.value]);
  } else {
    const enabledSet = new Set(enabledKeys.value);
    commitKeys(checkedKeys.value.filter((key) => !enabledSet.has(key)));
  }
}

/** 分组全选/清空（仅操作组内可交互项，其余分组保持不变） */
function toggleSection(section, checked) {
  const enabledSet = new Set(
    section.options.filter((opt) => props.disabled !== true && opt?.disabled !== true).map((opt) => keyOf(opt.value)),
  );
  if (checked) {
    commitKeys([...checkedKeys.value, ...enabledSet]);
  } else {
    commitKeys(checkedKeys.value.filter((key) => !enabledSet.has(key)));
  }
}

function onUpdate(values) {
  commitKeys(values || []);
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

  &__section {
    margin-bottom: 12px;

    &:last-child {
      margin-bottom: 0;
    }
  }

  &__head {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 4px;
  }

  &__num {
    color: var(--prev-color-text-secondary);
    font-size: 12px;
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
