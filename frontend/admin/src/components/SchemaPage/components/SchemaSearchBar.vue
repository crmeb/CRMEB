<template>
  <el-form :inline="true" class="schema-search-bar" @submit.prevent>
    <template v-for="field in fields" :key="field.field">
      <!-- 文本输入 -->
      <el-form-item v-if="field.type === 'input'" :label="field.label">
        <el-input
          v-model="model[field.field]"
          :placeholder="field.placeholder || '请输入' + field.label"
          clearable
          class="schema-search-item"
          @keyup.enter="onSearch"
        />
      </el-form-item>
      <!-- 数值输入 -->
      <el-form-item v-else-if="field.type === 'number'" :label="field.label">
        <el-input-number
          v-model="model[field.field]"
          :placeholder="field.placeholder || '请输入' + field.label"
          :controls="false"
          class="schema-search-item"
          @keyup.enter="onSearch"
        />
      </el-form-item>
      <!-- 下拉选择（支持 options_url 远程加载；remote 声明时按关键字远程搜索） -->
      <el-form-item v-else-if="field.type === 'select'" :label="field.label">
        <el-select
          v-model="model[field.field]"
          :placeholder="field.placeholder || '请选择' + field.label"
          :multiple="!!field.multiple"
          collapse-tags
          clearable
          filterable
          :remote="!!field.remote"
          :remote-method="field.remote ? (query) => loadRemoteOptions(field, query) : undefined"
          :loading="!!remoteLoading[field.field]"
          class="schema-search-item"
        >
          <el-option v-for="opt in getOptions(field)" :key="String(opt.value)" :label="opt.label" :value="opt.value" />
        </el-select>
      </el-form-item>
      <!-- 单日期 -->
      <el-form-item v-else-if="field.type === 'date'" :label="field.label">
        <el-date-picker
          v-model="model[field.field]"
          type="date"
          value-format="YYYY-MM-DD"
          :placeholder="field.placeholder || '请选择' + field.label"
          class="schema-search-item"
        />
      </el-form-item>
      <!-- 日期范围（提交时拆分为 {field}_start / {field}_end；统一走全局 dateRangePicker，内置快捷项） -->
      <el-form-item v-else-if="field.type === 'daterange'" :label="field.label">
        <date-range-picker v-model="rangeModel[field.field]" width="200px" value-format="YYYY-MM-DD" />
      </el-form-item>
      <!-- 未知类型按文本输入降级渲染 -->
      <el-form-item v-else :label="field.label">
        <el-input v-model="model[field.field]" clearable class="schema-search-item" @keyup.enter="onSearch" />
      </el-form-item>
    </template>
    <el-form-item>
      <el-button type="primary" v-db-click @click="onSearch">搜索</el-button>
      <el-button v-db-click @click="onReset">重置</el-button>
    </el-form-item>
  </el-form>
</template>

<script setup>
/**
 * 通用页面搜索栏（独立组件，轻量声明式字段渲染）
 *
 * 字段配置来自页面 config.search，支持 input/number/select/date/daterange 五种封闭类型；
 * daterange 提交时自动拆分为 {field}_start / {field}_end 两个查询参数，面板内置快捷选项（今天/昨天/本月等）；
 * select 支持 options_url 远程加载选项（remote 声明时升级为按关键字远程搜索，接口需支持 keyword 参数）。
 */
import { reactive, watch } from 'vue';
import request from '@/libs/request';

defineOptions({ name: 'SchemaSearchBar' });

const props = defineProps({
  /** 搜索字段声明列表 [{field,label,type,options,options_url,remote,...}] */
  fields: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(['search']);

/** 单值字段模型（input/number/select/date） */
const model = reactive({});
/** 日期范围字段模型（daterange，值为 [start, end] 数组） */
const rangeModel = reactive({});
/** 远程选项缓存（field => [{label,value}]） */
const remoteOptions = reactive({});
/** 远程选项加载状态（field => boolean） */
const remoteLoading = reactive({});

// 字段声明变化（页面切换）时重建模型
watch(
  () => props.fields,
  (fields) => {
    Object.keys(model).forEach((k) => delete model[k]);
    Object.keys(rangeModel).forEach((k) => delete rangeModel[k]);
    fields.forEach((field) => {
      if (field.type === 'daterange') {
        rangeModel[field.field] = null;
      } else {
        model[field.field] = field.type === 'select' && field.multiple ? [] : null;
      }
      // 非远程搜索的远程下拉：挂载即全量加载选项
      if (field.type === 'select' && field.options_url && !field.remote) {
        loadRemoteOptions(field);
      }
    });
  },
  { immediate: true },
);

/**
 * 加载远程下拉选项
 * @param {object} field 搜索字段声明
 * @param {string} [keyword] 关键字（remote 模式传给接口的查询参数）
 * @returns {Promise<void>}
 */
async function loadRemoteOptions(field, keyword) {
  const key = field.field;
  remoteLoading[key] = true;
  try {
    const res = await request({ url: field.options_url, method: 'get', params: keyword ? { keyword } : {} });
    const data = res?.data;
    // 响应约定：data 直接为选项数组，或为 {options: 选项数组}
    remoteOptions[key] = Array.isArray(data) ? data : Array.isArray(data?.options) ? data.options : [];
  } catch {
    // 静默降级为空选项，不打断页面渲染
    remoteOptions[key] = [];
  } finally {
    remoteLoading[key] = false;
  }
}

/**
 * 获取下拉选项：声明 options_url 时取远程缓存，否则取静态声明
 * @param {object} field 搜索字段声明
 * @returns {Array<{label,value}>}
 */
function getOptions(field) {
  return field.options_url ? remoteOptions[field.field] || [] : field.options || [];
}

/** 组装查询条件并触发搜索 */
function onSearch() {
  const where = {};
  props.fields.forEach((field) => {
    if (field.type === 'daterange') {
      const range = rangeModel[field.field];
      if (Array.isArray(range) && range.length === 2) {
        where[`${field.field}_start`] = range[0];
        where[`${field.field}_end`] = range[1];
      }
      return;
    }
    const value = model[field.field];
    // 空值不提交：''/null/undefined/空数组均过滤
    const isEmpty = value === '' || value === null || value === undefined || (Array.isArray(value) && !value.length);
    if (!isEmpty) {
      where[field.field] = value;
    }
  });
  emit('search', where);
}

/** 重置为初始空值 */
function onReset() {
  props.fields.forEach((field) => {
    if (field.type === 'daterange') {
      rangeModel[field.field] = null;
    } else {
      model[field.field] = field.type === 'select' && field.multiple ? [] : null;
    }
  });
  emit('search', {});
}
</script>

<style lang="scss" scoped>
.schema-search-bar {
  .schema-search-item {
    width: 200px;
  }
}
</style>
