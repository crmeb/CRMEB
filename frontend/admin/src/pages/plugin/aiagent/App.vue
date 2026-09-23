<template>
  <component :is="currentPage" v-if="currentPage" />
</template>

<script>
import { defineAsyncComponent } from 'vue';

const IndexPage = defineAsyncComponent(() => import('./pages/index'));

export default {
  name: 'ExtAiAgent',
  props: {
    section: { type: String, default: '' },
    action: { type: String, default: '' },
  },
  computed: {
    currentPage() {
      // Vue3 动态组件不支持异步工厂函数，必须经 defineAsyncComponent 包装
      return this.section === 'aiagent' && this.action === 'manage' ? IndexPage : null;
    },
  },
};
</script>
