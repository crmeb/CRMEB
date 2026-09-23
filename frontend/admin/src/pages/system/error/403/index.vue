<template>
  <error-content code="403" desc="抱歉，你无权访问该页面" :src="src" />
</template>

<script setup>
import error403 from '@/assets/images/error-page/error-401.svg';
import errorContent from '../error-content.vue';

defineOptions({ name: 'error_403' });

const src = error403;
</script>
