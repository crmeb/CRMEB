<template>
  <div class="schema-detail-page">
    <!-- 页面标题卡（全站统一风格，独立于内容卡片） -->
    <div v-if="config.title" class="i-layout-page-header header-title">
      <span class="ivu-page-header-title">{{ config.title }}</span>
    </div>
    <!-- 内容加载中 -->
    <div v-if="loading" v-loading="loading" class="schema-detail-page__loading" />
    <!-- 加载失败 -->
    <el-result v-else-if="loadError" icon="error" title="内容加载失败" :sub-title="loadError">
      <template #extra>
        <el-button type="primary" v-db-click @click="loadDetail">重试</el-button>
      </template>
    </el-result>
    <!-- 内容卡片：信息提示条 + 页面级动作 + 字段描述列表（json 字段自带一键复制） -->
    <el-card v-else shadow="never">
      <!-- 卡片顶部信息提示条 -->
      <el-alert
        v-if="config.tip"
        :title="config.tip"
        type="info"
        show-icon
        :closable="false"
        class="schema-detail-page__tip"
      />
      <div v-if="pageActions.length" class="schema-detail-page__actions">
        <el-button
          v-for="actionItem in pageActions"
          :key="actionItem.action"
          :type="actionItem.theme || 'primary'"
          v-db-click
          @click="onAction(actionItem)"
        >
          {{ actionItem.label }}
        </el-button>
      </div>
      <SchemaDetail :fields="fields" />
    </el-card>
  </div>
</template>

<script setup>
/**
 * 通用页面详情模板
 *
 * 承接 type=detail 的页面声明：单例只读内容页（全站风格标题卡 + 卡片内提示条 +
 * 字段描述列表 + 页面级动作），适用于配置说明、接入文档、单实体详情等场景。
 * 动作经确认交互（confirm_action/direct_action）调用网关 action 端点，
 * 响应含 refresh 指令（默认）时重新加载详情数据。
 */
import { ref, computed, watch } from 'vue';
import { useRoute } from 'vue-router';
import { ElMessage } from 'element-plus';
import SchemaDetail from '../components/SchemaDetail.vue';
import { schemaDetailApi, schemaActionApi } from '../api';
import { runAction, handleActionResponse } from '../composables/useSchemaAction';

defineOptions({ name: 'SchemaDetailTemplate' });

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

const route = useRoute();

/** 加载状态 */
const loading = ref(false);
const loadError = ref('');
/** 详情字段 [{label, value, type?, map?}] */
const fields = ref([]);

const emit = defineEmits(['refresh']);

/** 页面级动作（config.actions 声明，label 可由提供者按状态动态返回） */
const pageActions = computed(() => props.config.actions || []);

/**
 * 加载详情数据（单例页面 id 缺省 0，由提供者自行处理）
 * @returns {Promise<void>}
 */
async function loadDetail() {
  loading.value = true;
  loadError.value = '';
  try {
    const res = await schemaDetailApi(props.pluginKey, props.pageKey, {
      id: route.query.id ? Number(route.query.id) : 0,
    });
    fields.value = res.data?.fields || [];
  } catch (res) {
    loadError.value = res?.msg || '接口请求失败';
  } finally {
    loading.value = false;
  }
}

/**
 * 动作成功后的刷新：详情数据 + 通知父级重载 config
 *
 * config.actions 的文案可由提供者按当前状态动态生成（如「开启服务/关闭服务」），
 * 动作改变状态后必须同时刷新两处才能保持按钮与内容联动
 */
function refreshAfterAction() {
  loadDetail();
  emit('refresh');
}

/**
 * 执行页面级动作（含确认交互；响应 refresh 指令默认重载详情与配置）
 * @param {object} actionItem 动作配置 {action, label, mode, confirm}
 * @returns {Promise<void>}
 */
async function onAction(actionItem) {
  await runAction(actionItem, async () => {
    try {
      const res = await schemaActionApi(props.pluginKey, props.pageKey, {
        action: actionItem.action,
        payload: { id: route.query.id ? Number(route.query.id) : 0 },
      });
      handleActionResponse(res, refreshAfterAction);
    } catch (res) {
      ElMessage.error(res?.msg || '操作失败');
    }
  });
}

// 页面标识或编辑主键变化（同容器切换页面/切换详情对象）时重新加载（与 List/Form 模板行为一致）
watch(
  () => [props.pluginKey, props.pageKey, route.query.id],
  () => loadDetail(),
  { immediate: true },
);
</script>

<style lang="scss" scoped>
.schema-detail-page {
  &__tip {
    margin-bottom: 12px;
  }
  &__loading {
    min-height: 300px;
  }
  &__actions {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    margin-bottom: 12px;
  }
}
</style>
