<!--
  订单状态徽章基元
-->
<template>
  <text :class="['status-badge', 'status-' + statusCode]">
    {{ statusText }}
  </text>
</template>

<script setup>
defineOptions({ name: 'PrimitiveStatusBadge' });

defineProps({
  statusCode: { type: String, default: '' },
  statusText: { type: String, default: '' },
});
</script>

<style scoped>
.status-badge { font-size: 28rpx; font-weight: bold; }
.status-pending_payment { color: var(--view-theme, #e93323); }
.status-processing { color: #ff9900; }
.status-completed { color: #07c160; }
.status-canceled { color: #999; }
.status-refunding { color: #ff6600; }
.status-refunded { color: #999; }
</style>
