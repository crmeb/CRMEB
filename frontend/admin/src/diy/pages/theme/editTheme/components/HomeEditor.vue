<template>
  <div class="home-editor">
    <diy-index ref="diy" />
  </div>
</template>

<script setup>
import { ref } from 'vue';
import DiyIndex from '@diy/pages/devise/diyIndex.vue';

defineOptions({ name: 'HomeEditor' });

const diy = ref(null);

function preview() {
  diy.value && diy.value.preview && diy.value.preview();
}

function saveOnly() {
  diy.value && diy.value.saveConfig && diy.value.saveConfig(1);
}

function saveAndClose() {
  diy.value && diy.value.saveConfig && diy.value.saveConfig(2);
}

function saveTemplate() {
  diy.value && (diy.value.nameModal = true);
}

// 供父组件 EditTheme 调用（diy 用于新建主题后回写 pageId）
defineExpose({ diy, preview, saveOnly, saveAndClose, saveTemplate });
</script>

<style lang="scss" scoped>
.home-editor {
  height: 100%;
}
:deep(.i-layout-page-header.header-title) {
  display: none;
}
</style>
