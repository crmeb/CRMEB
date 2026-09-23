<template>
  <div>
    <to-day></to-day>
    <transaction-from></transaction-from>
  </div>
</template>

<script setup>
import toDay from './components/toDay';
import transactionFrom from './components/transaction';

defineOptions({ name: 'index' });
</script>

<style scoped></style>
