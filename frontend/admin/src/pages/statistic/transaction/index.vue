<template>
  <div class="statistic-transaction">
    <pages-header title="交易统计" description="查看今日交易概况与选定周期内的营业额、支付与支出趋势。" />
    <to-day />
    <transaction-from />
  </div>
</template>

<script setup>
import toDay from './components/toDay';
import transactionFrom from './components/transaction';

defineOptions({ name: 'statisticTransaction' });
</script>

<style scoped lang="scss">
.statistic-transaction {
  :deep(.ivu-mt),
  :deep(.ivu-mt-16) {
    margin-top: 16px;
  }
}
</style>
