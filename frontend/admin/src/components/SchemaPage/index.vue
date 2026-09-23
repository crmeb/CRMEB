<template>
  <div class="schema-page">
    <!-- 加载中 -->
    <div v-if="loading" v-loading="loading" class="schema-page__loading" />
    <!-- 加载失败 -->
    <el-result v-else-if="error" icon="error" title="页面加载失败" :sub-title="error">
      <template #extra>
        <el-button type="primary" v-db-click @click="loadConfig">重试</el-button>
      </template>
    </el-result>
    <!-- 列表页 -->
    <ListTemplate
      v-else-if="pageType === PAGE_TYPES.LIST"
      :plugin-key="pluginKey"
      :page-key="pageKey"
      :config="config"
    />
    <!-- 整页表单 -->
    <FormTemplate
      v-else-if="pageType === PAGE_TYPES.FORM"
      :plugin-key="pluginKey"
      :page-key="pageKey"
      :config="config"
    />
    <!-- 详情页 -->
    <DetailTemplate
      v-else-if="pageType === PAGE_TYPES.DETAIL"
      :plugin-key="pluginKey"
      :page-key="pageKey"
      :config="config"
      @refresh="loadConfig"
    />
    <!-- 统计页：待交付 -->
    <el-result
      v-else-if="pageType"
      icon="info"
      title="页面类型开发中"
      :sub-title="`类型 ${pageType} 将在后续版本支持`"
    />
  </div>
</template>

<script setup>
/**
 * 通用页面容器入口
 *
 * 承接通配路由 /admin/plugin/:pluginKey/:pageKey：
 * 拉取页面结构配置后按 type 分发到对应模板（list 列表页 / form 整页表单 /
 * detail 详情页，stat 统计页待交付）。
 */
import { ref, computed, watch } from 'vue';
import { useRoute } from 'vue-router';
import ListTemplate from './templates/ListTemplate.vue';
import FormTemplate from './templates/FormTemplate.vue';
import DetailTemplate from './templates/DetailTemplate.vue';
import { schemaConfigApi } from './api';
import { PAGE_TYPES } from './types';
import { setTitle } from '@/libs/util';
import { i18n } from '@/i18n/index.js';

defineOptions({ name: 'SchemaPage' });

const props = defineProps({
  /** 插件目录名（路由参数） */
  pluginKey: {
    type: String,
    required: true,
  },
  /** 页面标识（路由参数） */
  pageKey: {
    type: String,
    required: true,
  },
});

const loading = ref(false);
const error = ref('');
const config = ref({});

/** 当前路由（动态更新 meta.title 供多标签页/浏览器标题联动） */
const route = useRoute();

/** 页面类型 */
const pageType = computed(() => config.value.type || '');

/**
 * 拉取页面结构配置
 * @returns {Promise<void>}
 */
async function loadConfig() {
  loading.value = true;
  error.value = '';
  try {
    const res = await schemaConfigApi(props.pluginKey, props.pageKey);
    config.value = res.data || {};
    syncPageTitle();
  } catch (res) {
    error.value = res?.msg || '接口请求失败';
  } finally {
    loading.value = false;
  }
}

/**
 * 将 config.title 同步到路由 meta 与浏览器标签标题
 *
 * 通用页面容器是所有插件页面的共享宿主，路由 meta.title 为占位文案，
 * 加载到页面声明后以声明标题为准（tagsView 多标签与 document.title 联动）
 */
function syncPageTitle() {
  const title = (config.value.title || '').trim();
  if (!title || route.meta.title === title) return;
  route.meta.title = title;
  setTitle(route, { $t: (key, value) => i18n.global.t(key, value) });
}

// 路由参数变化（页面间切换）时重新加载
watch(() => [props.pluginKey, props.pageKey], loadConfig, { immediate: true });
</script>

<style lang="scss" scoped>
.schema-page {
  &__loading {
    min-height: 300px;
  }
}
</style>
