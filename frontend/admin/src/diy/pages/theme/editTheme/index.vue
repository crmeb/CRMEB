<template>
  <div class="edit-theme-layout">
    <!-- 左侧导航 -->
    <div class="layout-sidebar" :class="{ collapsed }" v-if="!isMicroPage">
      <sidebar
        :active-menu="activeMenu"
        :collapsed="collapsed"
        :unsaved="isDirty"
        @change="handleMenuChange"
        @save="handleSidebarSave"
        @toggle="collapsed = !collapsed"
      ></sidebar>
    </div>

    <!-- 右侧主体 -->
    <div class="layout-main">
      <!-- 顶部栏 -->
      <page-header
        :theme-name="themeName"
        :theme-info="themeInfo"
        :isMicroPage="isMicroPage"
        @preview="onPreview"
        @save="onSave"
        @save-close="onSaveClose"
        @save-template="onSaveTemplate"
        @update-info="handleUpdateInfo"
      ></page-header>

      <!-- 内容区域 -->
      <div class="layout-content">
        <style-config ref="styleConfig" v-if="activeMenu === 'theme'"></style-config>
        <home-editor ref="homeEditor" v-else-if="activeMenu === 'home'"></home-editor>
        <category-editor ref="categoryEditor" v-else-if="activeMenu === 'category'"></category-editor>
        <detail-editor ref="detailEditor" v-else-if="activeMenu === 'detail'"></detail-editor>
        <user-editor ref="userEditor" v-else-if="activeMenu === 'user'"></user-editor>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, provide, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import Sidebar from './components/Sidebar.vue';
import PageHeader from './components/Header.vue';
import StyleConfig from './components/StyleConfig.vue';
import HomeEditor from './components/HomeEditor.vue';
import CategoryEditor from './components/CategoryEditor.vue';
import DetailEditor from './components/DetailEditor.vue';
import UserEditor from './components/UserEditor.vue';
import { saveThemeTitle, themeInfo as themeInfoApi } from '@diy/api';

defineOptions({ name: 'EditTheme' });

const route = useRoute();
const router = useRouter();

const themeName = ref('请设置页面名称');
const themeInfo = ref('');
const activeMenu = ref('home'); // 默认选中商城首页
const collapsed = ref(false);
const isDirty = ref(false); // 是否有未保存的修改

// 各编辑器组件实例（子组件通过 defineExpose 暴露 diy / preview / saveOnly 等）
const styleConfig = ref(null);
const homeEditor = ref(null);
const categoryEditor = ref(null);
const detailEditor = ref(null);
const userEditor = ref(null);

const refMap = {
  // 键须与 getRefName() 返回值一致（ref 变量名）；
  // 此前误用 activeMenu 名（theme/home/...）作键，refMap[getRefName()] 恒为
  // undefined，顶栏四个操作按钮（预览/另存主题/保存/保存并关闭）全部静默失效
  styleConfig,
  homeEditor,
  categoryEditor,
  detailEditor,
  userEditor,
};

provide('setDirty', (dirty) => {
  isDirty.value = dirty;
});

watch(
  () => route.query.type,
  (val) => {
    if (val) {
      activeMenu.value = val;
    }
  },
  { immediate: true },
);

const isMicroPage = computed(() => route.query.page_type === 'micro');

onMounted(() => {
  if (route.query.id != 0) getThemeBaseInfo();
});

function getThemeBaseInfo() {
  const id = route.query.id || 0;
  if (!id) {
    themeName.value = '请设置页面名称';
    return;
  }
  themeInfoApi(id, 'base')
    .then((res) => {
      themeName.value = res.data.title || '请设置页面名称';
      themeInfo.value = res.data.info;
    })
    .catch((err) => {
      ElMessage.error(err.msg);
    });
}

function handleMenuChange(menuKey) {
  if (isDirty.value) {
    isDirty.value = false;
  }
  activeMenu.value = menuKey;
  if (route.query.type !== menuKey) {
    router.replace({ query: { ...route.query, type: menuKey } });
  }
}

function handleSidebarSave(key) {
  onSave();
}

function handleUpdateInfo(data) {
  themeName.value = data.title;
  themeInfo.value = data.info;
  let id = route.query.id || 0;
  saveThemeTitle(id, data)
    .then((res) => {
      ElMessage.success('保存成功');
      if (id == 0) {
        let query = { ...route.query, id: res.data.id };
        if (query.tid) {
          delete query.tid; // 保存后移除 tid
        }
        router.replace({ query });
        // Update active component's pageId
        const refName = getRefName();
        const comp = refName && refMap[refName] ? refMap[refName].value : null;
        if (comp && comp.diy) {
          comp.diy.pageId = res.data.id;
        }
      }
    })
    .catch((err) => {
      ElMessage.error(err.msg);
    });
}

function getRefName() {
  switch (activeMenu.value) {
    case 'theme':
      return 'styleConfig';
    case 'home':
      return 'homeEditor';
    case 'category':
      return 'categoryEditor';
    case 'detail':
      return 'detailEditor';
    case 'user':
      return 'userEditor';
    default:
      return '';
  }
}

function onPreview() {
  const refName = getRefName();
  const comp = refName && refMap[refName] ? refMap[refName].value : null;
  if (comp && comp.preview) {
    comp.preview();
  }
}

function onSave() {
  const refName = getRefName();
  const comp = refName && refMap[refName] ? refMap[refName].value : null;
  if (comp && comp.saveOnly) {
    comp.saveOnly();
  }
}

function onSaveClose() {
  const refName = getRefName();
  const comp = refName && refMap[refName] ? refMap[refName].value : null;
  if (comp && comp.saveAndClose) {
    comp.saveAndClose();
  }
}

function onSaveTemplate() {
  const refName = getRefName();
  const comp = refName && refMap[refName] ? refMap[refName].value : null;
  if (comp && comp.saveTemplate) {
    comp.saveTemplate();
  }
}
</script>
<style>
.el-main {
  padding: 0;
}
</style>
<style lang="scss" scoped>
.edit-theme-layout {
  display: flex;
  height: 100vh;
  background-color: #f5f7fa;
  overflow: hidden;

  .layout-sidebar {
    width: 120px;
    background: #fff;
    height: 100%;
    flex-shrink: 0;
    transition: width 0.2s ease;
    &.collapsed {
      width: 70px;
    }
  }

  .layout-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    min-width: 0;

    .layout-content {
      flex: 1;
      overflow: hidden;
      position: relative;
      background: #fff;
    }
  }
}
</style>
