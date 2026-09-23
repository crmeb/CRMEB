<template>
  <div
    class="ext-container"
    v-loading="loading"
    element-loading-text="扩展加载中..."
    element-loading-spinner="el-icon-loading"
  >
    <component
      :is="extComponent"
      v-if="extComponent"
      :key="componentKey"
      :section="section"
      :action="action"
      :params="params"
    />
    <div v-else-if="!loading" class="ext-error">
      <el-empty :description="'扩展「' + typeKey + '」加载失败，请确认扩展已安装'" />
      <el-button type="primary" @click="loadExtension">重新加载</el-button>
    </div>
  </div>
</template>

<script setup>
/**
 * 扩展容器
 * 运行时动态加载 UMD 编译的扩展包（JS + CSS），渲染为 Vue 动态组件。
 *
 * 扩展目录约定：{publicPath}/static/extensions/{typeKey}/
 *    - app.umd.js   UMD 构建产物
 *    - app.umd.css  UMD 样式文件
 *
 * 开发模式：直接通过 import.meta.glob（dev-extension-loader.js）加载扩展源码，支持 HMR。
 * 生产模式：注入 <script>/<link> 从 /admin/static/extensions/{key}/ 加载 UMD。
 */
import { ref, shallowRef, computed, watch, onBeforeUnmount } from 'vue';
import { useRoute } from 'vue-router';
import { ElMessage } from 'element-plus';
import { i18n } from '@/i18n/index.js';

defineOptions({ name: 'PluginPageContainer' });

const props = defineProps({
  typeKey: { type: String, required: true },
  section: { type: String, default: 'product' },
  action: { type: String, default: 'list' },
  params: { type: Object, default: () => ({}) },
});

const route = useRoute();
const loading = ref(true);
const extComponent = shallowRef(null);
const extRoutes = ref(null);
let scriptEl = null;

const componentKey = computed(() => `${props.typeKey}-${props.section}-${props.action}`);

function updateTitle() {
  const title = route.meta.title;
  if (title && i18n.global) {
    i18n.global.mergeLocaleMessage(i18n.global.locale, { [title]: title });
  }
}

function ensureLoaded() {
  const globalKey = `__ext_${props.typeKey}`;
  if (window[globalKey]) {
    applyModule(window[globalKey]);
  } else {
    loadExtension();
  }
}

function applyModule(mod) {
  extComponent.value = mod && mod.default ? mod.default : mod;
  if (mod && mod.routes) {
    extRoutes.value = mod.routes;
    updateTitle();
  }
}

function loadExtension() {
  loading.value = true;
  extComponent.value = null;
  const globalKey = `__ext_${props.typeKey}`;

  // 开发模式：直接 import 扩展源码（vite HMR 热更新）
  if (import.meta.env.DEV) {
    loadDevExtension().catch((e) => {
      loading.value = false;
      ElMessage.error(`扩展「${props.typeKey}」开发模式加载失败: ${e.message}`);
    });
    return;
  }

  const base = `/admin/static/extensions/${props.typeKey}/`;
  const cssUrl = base + 'app.umd.css';
  const jsUrl = base + 'app.umd.js';

  // 已加载过，直接使用
  if (window[globalKey]) {
    applyModule(window[globalKey]);
    loading.value = false;
    return;
  }

  // 加载 CSS
  if (!document.querySelector(`link[href="${cssUrl}"]`)) {
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = cssUrl;
    document.head.appendChild(link);
  }

  // 加载 JS
  const script = document.createElement('script');
  script.src = jsUrl;
  script.onload = () => {
    applyModule(window[globalKey]);
    loading.value = false;
  };
  script.onerror = () => {
    loading.value = false;
    ElMessage.error(`扩展「${props.typeKey}」脚本加载失败`);
  };
  document.body.appendChild(script);
  scriptEl = script;
}

async function loadDevExtension() {
  try {
    // 动态 import 独立 loader（生产构建被 vite 插件替换为 stub，扩展源码不进生产产物）
    const mod = await import('./dev-extension-loader').then((m) => m.loadDevExtension(props.typeKey));
    applyModule(mod);
    loading.value = false;
  } catch (e) {
    loading.value = false;
    ElMessage.error(`扩展「${props.typeKey}」加载失败: ${e.message}`);
  }
}

function cleanup() {
  if (scriptEl && scriptEl.parentNode) {
    scriptEl.parentNode.removeChild(scriptEl);
    scriptEl = null;
  }
}

watch(
  () => props.typeKey,
  () => {
    cleanup();
    loadExtension();
  },
);
watch(
  () => props.section,
  () => {
    updateTitle();
    ensureLoaded();
  },
);
watch(
  () => props.action,
  () => {
    updateTitle();
    ensureLoaded();
  },
);

loadExtension();

onBeforeUnmount(() => {
  cleanup();
});
</script>

<style scoped>
.ext-container {
  min-height: 400px;
  display: flex;
  flex-direction: column;
}
.ext-error {
  text-align: center;
  padding: 80px 0;
}
</style>
