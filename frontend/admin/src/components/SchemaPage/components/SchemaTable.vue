<template>
  <el-table
    v-loading="loading"
    :data="data"
    row-key="id"
    stripe
    @sort-change="onSortChange"
    @selection-change="onSelectionChange"
  >
    <!-- 多选列：仅声明批量操作时显示 -->
    <el-table-column v-if="batchActions.length" type="selection" width="46" />
    <el-table-column
      v-for="column in columns"
      :key="column.prop"
      :prop="column.prop"
      :label="column.label"
      :width="column.width"
      :min-width="column.minWidth"
      :align="column.align"
      :fixed="column.fixed"
      :show-overflow-tooltip="column.type === 'ellipsis' ? overflowTooltipOptions : false"
      :sortable="column.sortable ? 'custom' : false"
    >
      <template #default="{ row }">
        <SchemaTableCell :column="column" :row="row" @switch-change="onSwitchChange" />
      </template>
    </el-table-column>
    <!-- 操作列：行操作按钮（删除类动作用危险色；visible 声明行级显示条件） -->
    <el-table-column v-if="rowActions.length" label="操作" :width="actionColumnWidth" fixed="right">
      <template #default="{ row }">
        <template v-for="actionItem in rowActions" :key="actionItem.action">
          <el-button
            v-if="isActionVisible(actionItem, row)"
            :type="actionItem.theme || (actionItem.action === 'delete' ? 'danger' : 'primary')"
            link
            v-db-click
            @click="emit('row-action', { actionItem, row })"
          >
            {{ actionItem.label }}
          </el-button>
        </template>
      </template>
    </el-table-column>
    <template #empty>
      <el-empty description="暂无数据" :image-size="90" />
    </template>
  </el-table>
</template>

<script setup>
/**
 * 通用页面表格（独立组件）
 *
 * 基于 el-table 封装：按列定义分发渲染（SchemaTableCell），操作列按行操作配置输出链接按钮。
 */
import { computed } from 'vue';
import SchemaTableCell from './SchemaTableCell.vue';

defineOptions({ name: 'SchemaTable' });

const props = defineProps({
  /** 列定义列表 */
  columns: {
    type: Array,
    default: () => [],
  },
  /** 行数据 */
  data: {
    type: Array,
    default: () => [],
  },
  /** 行操作配置 [{action,label,mode,confirm,theme}] */
  rowActions: {
    type: Array,
    default: () => [],
  },
  /** 批量操作配置 [{action,label,mode,confirm,theme}]，非空时展示多选列 */
  batchActions: {
    type: Array,
    default: () => [],
  },
  /** 加载状态 */
  loading: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['row-action', 'switch-change', 'sort-change', 'selection-change']);

/** 操作列宽度：按按钮数量与文案长度估算 */
const actionColumnWidth = computed(() => {
  const totalChars = props.rowActions.reduce((sum, item) => sum + (item.label || '').length, 0);
  return Math.max(100, totalChars * 15 + 60);
});

/** 超长文本列的气泡配置：通过 popperClass 限定最大宽度（样式见文件底部全局样式块） */
const overflowTooltipOptions = { popperClass: 'schema-table-tooltip' };

/**
 * 行操作显示条件判断（visible: [{field, value, op}] 全部满足才显示）
 * @param {object} actionItem 动作配置
 * @param {object} row 行数据
 * @returns {boolean}
 */
function isActionVisible(actionItem, row) {
  const conditions = actionItem.visible || [];
  if (!conditions.length) return true;
  return conditions.every(({ field, value, op = 'eq' }) => {
    const rowValue = row[field];
    const isEmpty = rowValue === '' || rowValue === null || rowValue === undefined;
    switch (op) {
      case 'eq':
        return String(rowValue) === String(value);
      case 'neq':
        return String(rowValue) !== String(value);
      case 'gt':
        return Number(rowValue) > Number(value);
      case 'lt':
        return Number(rowValue) < Number(value);
      case 'gte':
        return Number(rowValue) >= Number(value);
      case 'lte':
        return Number(rowValue) <= Number(value);
      case 'empty':
        return isEmpty;
      case 'not_empty':
        return !isEmpty;
      default:
        return true;
    }
  });
}

/** 开关列切换透传 */
function onSwitchChange(payload) {
  emit('switch-change', payload);
}

/** 多选变化透传（rows 为当前选中行数据数组） */
function onSelectionChange(rows) {
  emit('selection-change', rows);
}

/**
 * 列排序透传（仅声明 sortable 的列触发；取消排序时 order 传空对象恢复默认排序）
 * @param {object} param0 {prop, order} order: ascending/descending/null
 */
function onSortChange({ prop, order }) {
  emit('sort-change', {
    prop,
    order: order ? { [prop]: order === 'ascending' ? 'asc' : 'desc' } : {},
  });
}
</script>

<style lang="scss" scoped>
/* 操作列文字按钮：强制无背景无边框（项目主题改写了按钮变量体系，link 态被实心样式覆盖） */
:deep(.el-button.is-link) {
  background: transparent !important;
  border: none !important;
  height: auto;
  padding: 0 2px !important;
}
:deep(.el-button.is-link.el-button--primary) {
  color: var(--el-color-primary) !important;
}
:deep(.el-button.is-link.el-button--danger) {
  color: var(--el-color-danger) !important;
}
:deep(.el-button.is-link.el-button--success) {
  color: var(--el-color-success) !important;
}
:deep(.el-button.is-link.el-button--warning) {
  color: var(--el-color-warning) !important;
}
</style>

<style lang="scss">
/* 表格超长文本气泡（append-to-body 渲染在 body 下，scoped 样式不生效）：
   限制最大宽度，避免长描述撑满整屏 */
.el-popper.schema-table-tooltip {
  max-width: min(480px, calc(100vw - 40px));
  line-height: 20px;
}
</style>
