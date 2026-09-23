<template>
  <div class="cardkey-app">
    <div class="section-content">
      <component :is="actionComponent" />
    </div>
  </div>
</template>

<script setup>
/**
 * 卡密商品扩展根组件
 * 由 PageContainer 动态加载，根据 action 切换子页面。
 * 路由参数（editId/orderNo）由子页面从 $route.params 自行读取，路由 path 保持不变。
 */
import { computed } from 'vue';
import ProductList from './index.vue';
import ProductAdd from './add/index.vue';
import StockList from './stock/List.vue';
import OrderList from './order/List.vue';
import OrderDetail from './order/Detail.vue';

const actionComponents = {
  list: ProductList,
  add: ProductAdd,
  stock: StockList,
  order: OrderList,
  order_detail: OrderDetail,
};

defineOptions({ name: 'CardkeyApp' });

const props = defineProps({
  section: { type: String, default: 'cardkey' },
  action: { type: String, default: 'list' },
  params: { type: Object, default: () => ({}) },
});

const actionComponent = computed(() => actionComponents[props.action] || ProductList);
</script>

<style scoped>
.cardkey-app {
  height: 100%;
}
</style>
