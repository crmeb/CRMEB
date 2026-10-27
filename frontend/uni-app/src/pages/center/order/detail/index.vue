<template>
  <view>
    <OrderDetailEngine
      ref="engine"
      :order-no="orderNo"
      :pending-invoice-id="pendingInvoiceId"
      @invoice-overlay="onInvoiceOverlay"
      @pending-invoice-consumed="pendingInvoiceId = ''"
    />
    <!-- 系统悬浮菜单（小程序端不显示）；开票弹层打开时隐藏，避免压在弹层上 -->
    <!-- #ifndef MP -->
    <home v-show="!invoiceOverlayOpen"></home>
    <!-- #endif -->
  </view>
</template>

<script setup>
import { ref } from 'vue';
import { onLoad, onShow } from '@dcloudio/uni-app';
import OrderDetailEngine from '@/dynamic-pages/engines/OrderDetailEngine.vue';
import home from '@/components/home';

const orderNo = ref('');
/** 从发票抬头页带回的待选抬头 ID，用于自动打开开票弹层 */
const pendingInvoiceId = ref('');
const engine = ref(null);
const invoiceOverlayOpen = ref(false);

function onInvoiceOverlay(open) {
  invoiceOverlayOpen.value = !!open;
}

onLoad((options) => {
  orderNo.value = options.order_no || '';
  pendingInvoiceId.value = options.invoice_id ? String(options.invoice_id) : '';
});

// 首次 onShow 与 onLoad 引擎的首次加载重复，跳过；此后每次返回本页（评价完成 navigateBack 等）刷新详情数据
let showed = false;
onShow(() => {
  if (!showed) {
    showed = true;
    return;
  }
  engine.value?.refresh();
});
</script>
