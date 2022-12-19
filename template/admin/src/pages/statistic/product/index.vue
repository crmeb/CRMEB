<template>
  <div>
    <product-info></product-info>
    <product-ranking></product-ranking>
  </div>
</template>

<script>
import productInfo from './components/productInfo';
import productRanking from './components/productRanking';
export default {
  name: 'index',
  components: {
    productInfo,
    productRanking,
  },
};
</script>

<style scoped></style>
