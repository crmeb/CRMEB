<template>
  <div>
    <div :id="echartsId" :style="styles" />
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue';
// 统一从按需注册入口引入（echarts/core）
import * as echarts from '@/libs/echarts';

defineOptions({ name: 'Index' });

const props = defineProps({
  styles: {
    type: Object,
    default: null,
  },
  optionData: {
    type: Object,
    default: null,
  },
});

let myChart = null;

const echartsId = computed(() => {
  return 'echarts' + Math.ceil(Math.random() * 100);
});

watch(
  () => props.optionData,
  (newVal, oldVal) => {
    handleSetVisitChart();
  },
  { deep: true }, // 对象内部属性的监听，关键。
);

onMounted(() => {
  nextTick(() => {
    handleSetVisitChart();
    window.addEventListener('resize', wsFunc);
  });
});

onBeforeUnmount(() => {
  window.removeEventListener('resize', wsFunc);
  if (!myChart) {
    return;
  }
  myChart.dispose();
  myChart = null;
});

function wsFunc() {
  myChart.resize();
}
function handleSetVisitChart() {
  myChart = echarts.init(document.getElementById(echartsId.value));
  let option = null;
  option = props.optionData;
  // 基于准备好的dom，初始化echarts实例
  myChart.setOption(option, true);
}

// 供父组件通过 ref 调用（原 Options API 方法默认公开，script setup 需显式暴露）
defineExpose({ wsFunc, handleSetVisitChart });
</script>

<style scoped></style>
