<template>
  <div>
    <div :id="echartsId" :style="styles" />
  </div>
</template>

<script setup>
import { computed, watch, onMounted, onBeforeUnmount, nextTick, getCurrentInstance } from 'vue';
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

// 组件实例 uid 保证同一页面多个图表容器 id 唯一，避免随机数碰撞导致图表渲染到错误节点
const instance = getCurrentInstance();
const echartsId = computed(() => `echarts-new-${instance ? instance.uid : Date.now()}`);

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
  const dom = document.getElementById(echartsId.value);
  if (!dom || !props.optionData) return;
  // 复用实例，避免数据刷新时重复 init
  if (!myChart) {
    myChart = echarts.init(dom);
  }
  myChart.setOption(props.optionData, true);
}

// 供父组件通过 ref 调用（原 Options API 方法默认公开，script setup 需显式暴露）
defineExpose({ wsFunc, handleSetVisitChart });
</script>

<style scoped></style>
