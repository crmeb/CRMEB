<template>
  <keep-alive :include="cacheList" :exclude="notCacheName">
    <router-view ref="child" />
  </keep-alive>
</template>
<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import { useAppStore } from '@/store/module/app';
import { storeToRefs } from 'pinia';

defineOptions({ name: 'ParentView' });

const route = useRoute();
const { tagNavList } = storeToRefs(useAppStore());

const notCacheName = computed(() => [route.meta && route.meta.notCache ? route.name : '']);
const cacheList = computed(() => [
  'ParentView',
  ...(tagNavList.value.length
    ? tagNavList.value.filter((item) => !(item.meta && item.meta.notCache)).map((item) => item.name)
    : []),
]);
</script>
