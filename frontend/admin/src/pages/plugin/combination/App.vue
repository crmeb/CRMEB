<template>
  <div class="combination-app">
    <div class="section-content">
      <component :is="sectionComponent" :params="params" />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import ProductList from './pages/product/index.vue';
import PinkList from './pages/pink/index.vue';
import CreateProduct from './pages/create/index.vue';
import Statistics from './pages/statistics/index.vue';

defineOptions({ name: 'CombinationApp' });

const props = defineProps({
  section: { type: String, default: 'combination' },
  action: { type: String, default: 'product' },
  params: { type: Object, default: () => ({}) },
});

const actionComponents = {
  product: ProductList,
  pink: PinkList,
  create: CreateProduct,
  statistics: Statistics,
};

const sectionComponent = computed(() => actionComponents[props.action] || ProductList);
</script>

<style scoped>
.combination-app {
  height: 100%;
}
</style>
