<template>
  <error-content code="500" desc="抱歉，服务器出错了" :src="src" />
</template>

<script setup>
import error500 from '@/assets/images/error-page/error-500.svg';
import errorContent from '../error-content.vue';

defineOptions({ name: 'error_500' });

const src = error500;
</script>
