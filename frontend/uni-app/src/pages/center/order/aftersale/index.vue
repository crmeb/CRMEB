<template>
  <AfterSaleEngine :options="loadOptions" />
</template>

<script setup>
import { ref } from 'vue';
import { onLoad } from '@dcloudio/uni-app';

/**
 * 售后页（双模式入口，薄包装）
 * - 带 order_no 参数：进入申请模式（填写售后申请表单）
 * - 带 after_sale_no 参数：进入详情模式（查看售后进度 + 进行中操作）
 * 退款/售后统一走 AfterSaleEngine，由后端 available_types 下发可用售后类型
 * 引擎组件收不到页面 onLoad，由本页响应式传参，引擎监听参数就绪后初始化。
 */
import AfterSaleEngine from '@/dynamic-pages/engines/AfterSaleEngine.vue';

// null 表示 onLoad 尚未触发，空对象表示路由确实缺少参数。
const loadOptions = ref(null);

onLoad((options) => {
  loadOptions.value = options || {};
});

</script>
