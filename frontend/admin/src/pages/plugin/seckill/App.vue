<template>
  <div class="seckill-app">
    <div class="section-content">
      <component :is="sectionComponent" :params="params" />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import ActivityList from './pages/activity/index.vue';
import ActivityCreate from './pages/activity/create.vue';
import ProductList from './pages/product/index.vue';
import ProductCreate from './pages/product/create.vue';
import Statistics from './pages/statistics/index.vue';

const actionComponents = {
  activity: ActivityList,
  activity_create: ActivityCreate,
  product: ProductList,
  product_create: ProductCreate,
  statistics: Statistics,
};

defineOptions({ name: 'SeckillApp' });

const props = defineProps({
  section: { type: String, default: 'seckill' },
  action: { type: String, default: 'activity' },
  params: { type: Object, default: () => ({}) },
});

const sectionComponent = computed(() => actionComponents[props.action] || ActivityList);
</script>

<style scoped>
.seckill-app {
  height: 100%;
}
</style>
