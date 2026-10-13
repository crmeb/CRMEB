<template>
  <OrderListEngine ref="engine" :status-filter="statusFilter" />
</template>

<script setup>
import { ref } from 'vue';
import { onLoad, onShow } from '@dcloudio/uni-app';
import OrderListEngine from '@/dynamic-pages/engines/OrderListEngine.vue';

// 旧版数字 status → 新版语义化 status 映射表
// 旧：0待付款 / 1待发货 / 2待收货 / 3待评价 / 9全部
// 新：pending_payment 待支付 / pending_delivery 待收货(实物) / pending_use 待使用(非实物) / pending_comment 待评价 / after_sale 退款售后 / all 全部
// 旧数字 1/2 均为实物语义（待发货/待收货），统一映射到 pending_delivery
const STATUS_MAP = {
  '0': 'pending_payment',
  '1': 'pending_delivery',
  '2': 'pending_delivery',
  '3': 'pending_comment',
  '9': 'all',
};

// 新版语义化状态白名单，命中则直接透传
const SEMANTIC_STATUS = ['all', 'pending_payment', 'pending_delivery', 'pending_use', 'pending_comment', 'after_sale'];

const statusFilter = ref('all');
const engine = ref(null);

onLoad((options) => {
  statusFilter.value = normalizeStatus(options.status);
});

// 首次 onShow 与引擎首次加载重复，跳过；此后每次返回本页（详情页评价/收货后返回）刷新列表数据
let showed = false;
onShow(() => {
  if (!showed) {
    showed = true;
    return;
  }
  engine.value?.refresh();
});

/**
 * 归一化 status 参数
 * 兼容旧版数字 status（0/1/2/3/9）与新版语义化字符串
 * @param {string} status URL 传入的 status 原始值
 * @returns {string} 语义化状态标识
 */
function normalizeStatus(status) {
  if (!status && status !== 0) return 'all';
  const key = String(status);
  // 命中语义化白名单直接透传
  if (SEMANTIC_STATUS.includes(key)) return key;
  // 命中旧版数字映射
  if (STATUS_MAP.hasOwnProperty(key)) return STATUS_MAP[key];
  return 'all';
}
</script>
