<template>
  <component :is="currentPage" v-if="currentPage" />
  <div v-else>页面不存在</div>
</template>

<script setup>
import { ref, watch } from 'vue';

const pageMap = {
  list: () => import('./pages/lotteryList'),
  config: () => import('./pages/config'),
};

defineOptions({ name: 'ExtLottery' });

const props = defineProps({ section: String, action: String });

const currentPage = ref(null);

watch(
  () => props.action,
  (val) => {
    const loader = pageMap[val];
    if (loader) {
      loader().then((comp) => {
        currentPage.value = comp.default;
      });
    }
  },
  { immediate: true },
);
</script>
