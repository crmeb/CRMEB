<template>
  <div class="normal-app">
    <div class="section-content">
      <component :is="sectionComponent" :edit-id="editId" :order-no="orderNo" />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import ProductList from './product/List.vue';
import ProductAdd from './add/index.vue';
import OrderList from './order/List.vue';
import OrderDetail from './order/Detail.vue';
import StatisticIndex from './statistic/Index.vue';

defineOptions({ name: 'normalApp' });

const sectionComponents = {
  product: ProductList,
  order: OrderList,
  statistic: StatisticIndex,
};

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
  if (props.section === 'statistic') {
    return StatisticIndex;
  }
  return sectionComponents[props.section] || ProductList;
});
const editId = computed(() => (props.params && props.params.id ? props.params.id : 0));
const orderNo = computed(() => (props.params && props.params.orderNo ? props.params.orderNo : ''));
</script>

<style scoped>
.normal-app {
  height: 100%;
}
</style>
