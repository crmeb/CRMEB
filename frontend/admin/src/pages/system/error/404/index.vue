<template>
  <error-content code="404" desc="抱歉，你访问的页面不存在" :src="src" />
</template>

<script setup>
import error404 from '@/assets/images/error-page/error-404.svg';
import errorContent from '../error-content.vue';

defineOptions({ name: 'error_404' });

const src = error404;
</script>
