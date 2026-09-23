<template>
  <div class="virtual-app">
    <div class="section-content">
      <component :is="sectionComponent" :edit-id="editId" :order-no="orderNo" />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import ProductList from './product/List.vue';
import ProductAdd from './product/Add.vue';
import OrderList from './order/List.vue';
import OrderDetail from './order/Detail.vue';

const sectionComponents = {
  product: ProductList,
  order: OrderList,
};

defineOptions({ name: 'virtualApp' });

const props = defineProps({
  section: { type: String, default: 'product' },
  action: { type: String, default: 'list' },
  params: { type: Object, default: () => ({}) },
});

const sectionComponent = computed(() => {
  if (props.section === 'product' && props.action === 'add') {
    return ProductAdd;
  }
  if (props.section === 'order' && props.action === 'detail') {
    return OrderDetail;
  }
  return sectionComponents[props.section] || ProductList;
});
const editId = computed(() => (props.params && props.params.id ? props.params.id : 0));
const orderNo = computed(() => (props.params && props.params.orderNo ? props.params.orderNo : ''));
</script>

<style scoped>
.virtual-app {
  height: 100%;
}
</style>
