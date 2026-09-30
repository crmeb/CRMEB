<template>
  <div class="schema-list">
    <!-- 统计卡片行（config.cards 声明，数据来自 list 响应 summary） -->
    <SchemaCards :cards="config.cards || []" :data="summary" />

    <!-- 搜索区 -->
    <el-card v-if="config.search && config.search.length" shadow="never" class="schema-list__search">
      <SchemaSearchBar :fields="config.search" @search="onSearch" />
    </el-card>

    <!-- 表格 + 分页 -->
    <el-card shadow="never" class="schema-list__table">
      <!-- 状态分类头（config.tabs 声明，切换以 where[field] 过滤） -->
      <el-tabs v-if="tabsConfig" v-model="activeTab" class="schema-list__tabs" @tab-change="onTabChange">
        <el-tab-pane v-for="opt in tabsConfig.options" :key="String(opt.value)" :name="String(opt.value)">
          <template #label>{{ opt.label }}{{ tabCountSuffix(opt) }}</template>
        </el-tab-pane>
      </el-tabs>
      <!-- 顶部操作区（与表格同卡：有分类头时在其下方，无分类头时在表格上方，还原常规后台页布局） -->
      <div v-if="topActions.length || batchActions.length" class="schema-list__actions">
        <el-button
          v-for="actionItem in topActions"
          :key="'top-' + actionItem.action"
          :type="actionItem.theme || 'primary'"
          v-db-click
          @click="onTopAction(actionItem)"
        >
          {{ actionLabel(actionItem) }}
        </el-button>
        <el-divider v-if="topActions.length && batchActions.length" direction="vertical" />
        <el-button
          v-for="actionItem in batchActions"
          :key="'batch-' + actionItem.action"
          :type="actionItem.theme || (actionItem.action === 'delete' ? 'danger' : 'primary')"
          plain
          v-db-click
          @click="onBatchAction(actionItem)"
        >
          {{ actionLabel(actionItem) }}
        </el-button>
      </div>
      <SchemaTable
        :columns="config.columns || []"
        :data="tableData"
        :row-actions="config.row_actions || []"
        :batch-actions="batchActions"
        :loading="loading"
        @row-action="onRowAction"
        @switch-change="onSwitchChange"
        @sort-change="onSortChange"
        @selection-change="onSelectionChange"
      />
      <div class="schema-list__page">
        <pagination v-if="total" :total="total" v-model:page="page" v-model:limit="limit" @pagination="loadList" />
      </div>
    </el-card>

    <!-- 表单抽屉（新增/编辑） -->
    <SchemaDrawer
      v-model="formDrawer.visible"
      :title="formDrawer.title"
      :submitting="formDrawer.submitting"
      @submit="onFormSubmit"
    >
      <SchemaForm v-if="formDrawer.schema" :schema="formDrawer.schema" ref="schemaFormRef" />
    </SchemaDrawer>

    <!-- 详情抽屉 -->
    <SchemaDrawer v-model="detailDrawer.visible" :title="detailDrawer.title" :show-footer="false">
      <SchemaDetail :fields="detailDrawer.fields" />
    </SchemaDrawer>
  </div>
</template>

<script setup>
/**
 * 通用页面列表模板
 *
 * 组合搜索栏（SchemaSearchBar）+ 表格（SchemaTable）+ 分页 + 右侧抽屉（新增/编辑/详情），
 * 处理顶部动作与行动作（drawer_form/drawer_detail/confirm_action/direct_action/navigate），
 * 全部数据经通用页面网关读写。
 */
import { ref, reactive, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import request from '@/libs/request';
import SchemaSearchBar from '../components/SchemaSearchBar.vue';
import SchemaTable from '../components/SchemaTable.vue';
import SchemaDrawer from '../components/SchemaDrawer.vue';
import SchemaForm from '../components/SchemaForm.vue';
import SchemaDetail from '../components/SchemaDetail.vue';
import SchemaCards from '../components/SchemaCards.vue';
import { schemaListApi, schemaFormApi, schemaDetailApi, schemaSaveApi, schemaActionApi } from '../api';
import { ACTION_MODES } from '../types';
import { runAction, handleActionResponse } from '../composables/useSchemaAction';

defineOptions({ name: 'SchemaListTemplate' });

const props = defineProps({
  /** 插件目录名 */
  pluginKey: {
    type: String,
    required: true,
  },
  /** 页面标识 */
  pageKey: {
    type: String,
    required: true,
  },
  /** 页面结构配置（已通过 config 端点获取） */
  config: {
    type: Object,
    required: true,
  },
});

const router = useRouter();

/** 列表状态 */
const tableData = ref([]);
const total = ref(0);
const page = ref(1);
const limit = ref(15);
const loading = ref(false);
/** 当前搜索条件（重置分页用） */
const currentWhere = ref({});
/** 当前排序（初始取 config.default_order，列头排序事件覆盖） */
const currentOrder = ref({});
/** 统计卡片数据（list 响应 summary 键，SchemaCards 按 field 取值） */
const summary = ref(null);
/** 状态分类头计数（list 响应 tabs_count 键，键为选项 value 字符串形式） */
const tabsCount = ref({});

/** 状态分类头配置（未声明或 options 为空时为 null；后端未声明时下发空数组，JS 中为真值需显式排除） */
const tabsConfig = computed(() => {
  const tabs = props.config.tabs;
  return tabs && Array.isArray(tabs.options) && tabs.options.length ? tabs : null;
});

/** 当前选中分类（el-tabs name 为字符串，提交时按选项还原原始 value 类型） */
const activeTab = ref('');

/** 当前选中的分类选项 */
const currentTabOption = computed(() =>
  (tabsConfig.value?.options || []).find((item) => String(item.value) === activeTab.value),
);

/**
 * 动作按钮文案（声明 follow_tab 时拼接当前分类标签，随 tab 切换更新，
 * 如 label=添加 + 分类"连续签到奖励" → "添加连续签到奖励"）
 * @param {object} actionItem 动作配置
 * @returns {string}
 */
function actionLabel(actionItem) {
  if (actionItem.follow_tab && currentTabOption.value) {
    return (actionItem.label || '') + currentTabOption.value.label;
  }
  return actionItem.label || '';
}

/** 顶部动作（action=create 固定为新增抽屉，其余走动作处理） */
const topActions = computed(() => props.config.actions || []);

/** 批量操作（声明后表格出现多选列，payload 携带选中行 id 数组） */
const batchActions = computed(() => props.config.batch_actions || []);

/** 当前勾选的行数据 */
const selectedRows = ref([]);

/** 表单抽屉状态 */
const formDrawer = reactive({
  visible: false,
  title: '',
  submitting: false,
  schema: null,
  /** 编辑目标行 id，0 为新增 */
  editId: 0,
});
const schemaFormRef = ref(null);

/** 详情抽屉状态 */
const detailDrawer = reactive({
  visible: false,
  title: '详情',
  fields: [],
});

/**
 * 加载列表数据
 *
 * 排序优先级：列头排序事件 > config.default_order 声明 > 提供者自行决定
 * @returns {Promise<void>}
 */
async function loadList() {
  loading.value = true;
  try {
    const res = await schemaListApi(props.pluginKey, props.pageKey, {
      page: page.value,
      limit: limit.value,
      where: currentWhere.value,
      order: Object.keys(currentOrder.value).length ? currentOrder.value : props.config?.default_order || {},
    });
    tableData.value = res.data.list || [];
    total.value = res.data.count || 0;
    summary.value = res.data.summary || null;
    tabsCount.value = res.data.tabs_count || {};
  } finally {
    loading.value = false;
  }
}

/**
 * 分类头切换（以选中项原始 value 合并进搜索条件并重置到第一页）
 * @param {string} name el-tabs 的 name（选项 value 的字符串形式）
 * @returns {void}
 */
function onTabChange(name) {
  const opt = (tabsConfig.value?.options || []).find((item) => String(item.value) === name);
  if (!opt) return;
  currentWhere.value = { ...currentWhere.value, [tabsConfig.value.field]: opt.value };
  page.value = 1;
  loadList();
}

/**
 * 分类计数后缀（with_count 且计数已返回时显示）
 * @param {object} opt 分类选项
 * @returns {string}
 */
function tabCountSuffix(opt) {
  if (!tabsConfig.value?.with_count || tabsCount.value[String(opt.value)] === undefined) return '';
  return ` (${tabsCount.value[String(opt.value)]})`;
}

/**
 * 列头排序（覆盖当前排序并重置到第一页；再次点击取消排序时恢复默认）
 * @param {object} param0 {prop, order}
 * @returns {void}
 */
function onSortChange({ order }) {
  currentOrder.value = order;
  page.value = 1;
  loadList();
}

/**
 * 页面标识变化（同容器切换页面）或首次挂载时：重置查询状态并加载列表数据
 */
watch(
  () => [props.pluginKey, props.pageKey],
  () => {
    currentWhere.value = {};
    currentOrder.value = {};
    page.value = 1;
    // 分类头默认选中第一项并合并进查询条件
    const firstTab = (tabsConfig.value?.options || [])[0];
    activeTab.value = firstTab !== undefined ? String(firstTab.value) : '';
    if (firstTab !== undefined) {
      currentWhere.value = { [tabsConfig.value.field]: firstTab.value };
    }
    summary.value = null;
    tabsCount.value = {};
    loadList();
  },
  { immediate: true },
);

/**
 * 搜索（重置到第一页；合并当前选中分类条件，避免覆盖丢失 tabs 过滤导致界面与查询不一致）
 * @param {object} where 搜索条件
 * @returns {void}
 */
function onSearch(where) {
  const next = { ...where };
  if (tabsConfig.value && currentTabOption.value) {
    next[tabsConfig.value.field] = currentTabOption.value.value;
  }
  currentWhere.value = next;
  page.value = 1;
  loadList();
}

/**
 * 顶部动作分发（action=create 固定为新增抽屉；navigate 直接跳转；其余走服务端动作）
 * @param {object} actionItem 动作配置
 * @returns {void}
 */
function onTopAction(actionItem) {
  if (actionItem.action === 'create') {
    openFormDrawer(0);
    return;
  }
  if (actionItem.mode === ACTION_MODES.NAVIGATE) {
    router.push(buildNavigatePath(actionItem.path || '', {}));
    return;
  }
  executeAction(actionItem, {});
}

/**
 * 行动作分发
 * @param {object} param0 {actionItem, row}
 * @returns {void}
 */
function onRowAction({ actionItem, row }) {
  const { mode } = actionItem;
  if (mode === ACTION_MODES.DRAWER_FORM) {
    openFormDrawer(row.id);
  } else if (mode === ACTION_MODES.DRAWER_DETAIL) {
    openDetailDrawer(row);
  } else if (mode === ACTION_MODES.NAVIGATE) {
    router.push(buildNavigatePath(actionItem.path || '', row));
  } else {
    executeAction(actionItem, { id: row.id });
  }
}

/**
 * 表格多选变化
 * @param {Array<object>} rows 当前勾选的行数据数组
 * @returns {void}
 */
function onSelectionChange(rows) {
  selectedRows.value = rows;
}

/**
 * 批量操作分发：未勾选行时提示，勾选后以 {ids: [...]} 为 payload 执行动作
 * @param {object} actionItem 批量动作配置（mode 仅支持 confirm_action/direct_action）
 * @returns {void}
 */
function onBatchAction(actionItem) {
  if (!selectedRows.value.length) {
    ElMessage.warning('请先勾选要操作的数据');
    return;
  }
  executeAction(actionItem, { ids: selectedRows.value.map((row) => row.id) });
}

/**
 * 执行服务端动作（含确认交互与响应指令处理）
 * @param {object} actionItem 动作配置
 * @param {object} payload 动作参数
 * @returns {Promise<void>}
 */
async function executeAction(actionItem, payload) {
  await runAction(actionItem, async () => {
    const res = await schemaActionApi(props.pluginKey, props.pageKey, {
      action: actionItem.action,
      payload,
    });
    handleActionResponse(res, loadList);
  });
}

/**
 * 开关列切换（以开关目标值发起动作请求）
 * @param {object} param0 {column, row, value}
 * @returns {void}
 */
function onSwitchChange({ column, row, value }) {
  schemaActionApi(props.pluginKey, props.pageKey, {
    action: column.action || 'status',
    payload: { id: row.id, value },
  })
    .then((res) => handleActionResponse(res, loadList))
    .catch(() => loadList());
}

/**
 * 打开表单抽屉（新增/编辑）
 *
 * 页面声明分类头（tabs）时，新增请求附带当前选中分类值（tab 参数），
 * 供提供者决定新增表单的默认值（如奖励类型随分类头）
 *
 * @param {number} id 0=新增；>0=编辑回填
 * @returns {Promise<void>}
 */
async function openFormDrawer(id) {
  try {
    const params = { id };
    if (!id && tabsConfig.value && currentTabOption.value) {
      params.tab = currentTabOption.value.value;
    }
    const res = await schemaFormApi(props.pluginKey, props.pageKey, params);
    formDrawer.schema = res.data;
    formDrawer.title = res.data.title || (id ? '编辑' : '新增');
    formDrawer.editId = id;
    formDrawer.visible = true;
  } catch (res) {
    ElMessage.error(res?.msg || '获取表单失败');
  }
}

/**
 * 表单提交：schema.action 为空默认提交网关 save 端点，否则按后端声明的 action/method 提交
 * @returns {Promise<void>}
 */
async function onFormSubmit() {
  if (formDrawer.submitting || !schemaFormRef.value) return;
  let formData;
  try {
    formData = await schemaFormRef.value.submit();
  } catch {
    return; // 表单校验未通过
  }
  formDrawer.submitting = true;
  const schema = formDrawer.schema || {};
  const requestPromise = schema.action
    ? request({ url: schema.action, method: schema.method || 'POST', data: formData })
    : schemaSaveApi(props.pluginKey, props.pageKey, { id: formDrawer.editId, ...formData });
  requestPromise
    .then((res) => {
      ElMessage.success(res?.msg || '保存成功');
      formDrawer.visible = false;
      loadList();
    })
    .catch((res) => ElMessage.error(res?.msg || '保存失败'))
    .finally(() => {
      formDrawer.submitting = false;
    });
}

/**
 * 打开详情抽屉
 * @param {object} row 行数据
 * @returns {Promise<void>}
 */
async function openDetailDrawer(row) {
  try {
    const res = await schemaDetailApi(props.pluginKey, props.pageKey, { id: row.id });
    detailDrawer.fields = res.data.fields || [];
    detailDrawer.title = '详情';
    detailDrawer.visible = true;
  } catch (res) {
    ElMessage.error(res?.msg || '获取详情失败');
  }
}

/**
 * 组装跳转路径（支持 :字段 占位符替换行数据）
 * @param {string} path 路径模板（如 /admin/plugin/mcp/log/:id）
 * @param {object} row 行数据
 * @returns {string}
 */
function buildNavigatePath(path, row) {
  return path.replace(/:(\w+)/g, (match, key) => row[key] ?? match);
}

defineExpose({ loadList });
</script>

<style lang="scss" scoped>
.schema-list {
  &__search {
    margin-bottom: 12px;
    :deep(.el-card__body) {
      padding-bottom: 2px;
    }
  }
  &__actions {
    // 动作区为卡内首个元素时抵消 20px 上内边距至 14px；跟在分类头后时不偏移
    margin-top: -6px;
    margin-bottom: 14px;
  }
  &__tabs + &__actions {
    margin-top: 0;
  }
  &__page {
    display: flex;
    justify-content: flex-end;
    margin-top: 12px;
  }
}
</style>
