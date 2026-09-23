<template>
  <div class="integral-app">
    <div class="section-content">
      <component :is="sectionComponent" :params="params" />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import ProductList from './pages/product/index.vue';
import ProductCreate from './pages/product/create.vue';
import OrderList from './pages/order/index.vue';
const actionComponents = {
  product: ProductList,
  product_create: ProductCreate,
  order: OrderList,
};

defineOptions({ name: 'IntegralApp' });

const props = defineProps({
  section: { type: String, default: 'integral' },
  action: { type: String, default: 'product' },
  params: { type: Object, default: () => ({}) },
});

const sectionComponent = computed(() => actionComponents[props.action] || ProductList);
</script>

<style scoped>
.integral-app {
  height: 100%;
}
</style>
