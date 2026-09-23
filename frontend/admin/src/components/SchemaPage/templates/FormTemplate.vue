<template>
  <div class="schema-form-page">
    <!-- 表单规则加载中 -->
    <div v-if="loading" v-loading="loading" class="schema-form-page__loading" />
    <!-- 表单规则加载失败 -->
    <el-result v-else-if="loadError" icon="error" title="表单加载失败" :sub-title="loadError">
      <template #extra>
        <el-button type="primary" v-db-click @click="loadForm">重试</el-button>
      </template>
    </el-result>
    <!-- 整页表单 -->
    <el-card v-else shadow="never">
      <template #header>
        <div class="schema-form-page__title">{{ config.title }}</div>
      </template>
      <div class="schema-form-page__body">
        <SchemaForm v-if="schema" ref="formRef" :schema="schema" />
        <div class="schema-form-page__footer">
          <el-button type="primary" :loading="submitting" v-db-click @click="onSubmit">
            {{ config.submit_text || '保存' }}
          </el-button>
          <el-button v-db-click @click="onReset">重置</el-button>
        </div>
      </div>
    </el-card>
  </div>
</template>

<script setup>
/**
 * 通用页面整页表单模板
 *
 * 承接 type=form 的页面声明：加载 Provider 的 form() 规则（create_form 标准结构）
 * 渲染整页表单，统一页脚提交。编辑场景经路由参数 ?id=xx 传入主键回填。
 * 提交目标与抽屉表单一致：schema.action 为空时默认提交网关 save 端点。
 */
import { ref, computed, watch } from 'vue';
import { useRoute } from 'vue-router';
import { ElMessage } from 'element-plus';
import request from '@/libs/request';
import SchemaForm from '../components/SchemaForm.vue';
import { schemaFormApi, schemaSaveApi } from '../api';
import { handleActionResponse } from '../composables/useSchemaAction';

defineOptions({ name: 'SchemaFormTemplate' });

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

/** 表单规则加载状态 */
const loading = ref(false);
const loadError = ref('');
/** 表单规则结构 {rules, title, action, method} */
const schema = ref(null);
/** 提交中 */
const submitting = ref(false);
const formRef = ref(null);

/** 编辑主键（路由 query.id，缺省 0 表示新增/单例表单） */
const currentId = computed(() => (route.query.id ? Number(route.query.id) : 0));

/**
 * 加载表单规则
 * @returns {Promise<void>}
 */
async function loadForm() {
  loading.value = true;
  loadError.value = '';
  try {
    const res = await schemaFormApi(props.pluginKey, props.pageKey, { id: currentId.value });
    schema.value = res.data || {};
  } catch (res) {
    loadError.value = res?.msg || '接口请求失败';
  } finally {
    loading.value = false;
  }
}

/**
 * 提交表单（校验由 SchemaForm 内部 form-create 处理）
 * @returns {Promise<void>}
 */
async function onSubmit() {
  if (submitting.value || !formRef.value) return;
  let formData;
  try {
    formData = await formRef.value.submit();
  } catch {
    return; // 表单校验未通过
  }
  submitting.value = true;
  const schemaObj = schema.value || {};
  const requestPromise = schemaObj.action
    ? request({ url: schemaObj.action, method: schemaObj.method || 'POST', data: { id: currentId.value, ...formData } })
    : schemaSaveApi(props.pluginKey, props.pageKey, { id: currentId.value, ...formData });
  requestPromise
    .then((res) => {
      // 整页表单保存后停留本页（refresh 无列表可刷，redirect/download 指令照常执行）
      handleActionResponse(res, null);
    })
    .catch((res) => ElMessage.error(res?.msg || '保存失败'))
    .finally(() => {
      submitting.value = false;
    });
}

/**
 * 重置表单（回退到最近一次加载的规则初值）
 * @returns {void}
 */
function onReset() {
  loadForm();
}

// 页面标识或编辑主键变化（同容器切换页面/切换编辑对象）时重新加载
watch(
  () => [props.pluginKey, props.pageKey, currentId.value],
  () => loadForm(),
  { immediate: true },
);
</script>

<style lang="scss" scoped>
.schema-form-page {
  &__loading {
    min-height: 300px;
  }
  &__title {
    font-size: 16px;
    font-weight: 600;
    color: var(--el-text-color-primary);
  }
  /* 整页表单限宽，与抽屉表单 size=720 保持一致的视觉宽度，避免字段拉伸过宽 */
  &__body {
    max-width: 720px;
  }
  &__footer {
    /* 与表单 labelWidth 对齐，使保存/重置按钮与输入框左边缘对齐 */
    padding-left: 105px;
    padding-top: 12px;
    border-top: 1px solid var(--el-border-color-lighter);
    margin-top: 8px;
  }
}
</style>
