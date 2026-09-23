<template>
  <div>
    <div :id="echartsId" style="height: 300px"></div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue';
// 统一从按需注册入口引入（echarts/core）
import * as echarts from '@/libs/echarts';

defineOptions({ name: 'index' });

const props = defineProps({
  infoList: {
    type: Object,
    default: null,
  },
  series: Array,
  echartsTitle: {
    type: String,
    default: '',
  },
  yAxisData: {
    type: Array,
    default: () => [],
  },
  bingXdata: Array,
});

let myChart = null;
const infoLists = ref(props.infoList);
const seriesArray = ref(props.series);

const echartsId = computed(() => {
  return 'echarts' + Math.ceil(Math.random() * 100);
});

watch(
  () => props.infoList,
  (newVal, oldVal) => {
    infoLists.value = newVal;
    handleSetVisitChart();
  },
  { deep: true }, // 对象内部属性的监听，关键。
);
watch(
  () => props.series,
  (newVal, oldVal) => {
    seriesArray.value = newVal;
    handleSetVisitChart();
  },
  { deep: true }, // 对象内部属性的监听，关键。
);

function wsFunc() {
  myChart.resize();
}

function handleSetVisitChart() {
  myChart = echarts.init(document.getElementById(echartsId.value));
  let option = null;
  if (props.echartsTitle === 'circle') {
    option = {
      tooltip: {
        trigger: 'item',
        formatter: '{a} <br/>{b} : {c} ({d}%)',
      },
      legend: {
        orient: 'vertical',
        left: 'right',
        data: infoLists.value.bing_xdata || [],
      },
      series: [
        {
          name: '访问来源',
          type: 'pie',
          radius: '70%',
          center: ['50%', '60%'],
          data: infoLists.value.bing_data || [],
          emphasis: {
            itemStyle: {
              shadowBlur: 10,
              shadowOffsetX: 0,
              shadowColor: 'rgba(0, 0, 0, 0.5)',
            },
          },
        },
      ],
    };
  } else {
    option = {
      tooltip: {
        trigger: 'axis',
      },
      toolbox: {},
      legend: {
        data: infoLists.value.legend || [],
      },
      color: ['#1495EB', '#00CC66', '#F9D249', '#ff9900', '#9860DF'],
      grid: {
        left: 16,
        right: 25,
        bottom: 10,
        top: 40,
        containLabel: true,
      },
      xAxis: [
        {
          type: 'category',
          axisLine: {
            lineStyle: {
              color: '#D7DDE4',
            },
          },
          axisTick: {
            alignWithLabel: true,
            lineStyle: {
              color: '#D7DDE4',
            },
          },
          splitLine: {
            show: false,
            lineStyle: {
              color: '#F5F7F9',
            },
          },
          // axisPointer: {
          //     type: 'shadow'
          // },
          axisLabel: {
            interval: 0,
            rotate: 40,
            textStyle: {
              color: '#7F8B9C',
            },
          },
          data: infoLists.value.xAxis,
        },
      ],
      yAxis: props.yAxisData.length
        ? props.yAxisData
        : {
            axisLine: {
              show: false,
            },
            axisTick: {
              show: false,
            },
            axisLabel: {
              textStyle: {
                color: '#7F8B9C',
              },
            },
            splitLine: {
              show: true,
              lineStyle: {
                color: '#F5F7F9',
              },
            },
            type: 'value',
          },
      series: seriesArray.value,
    };
  }
  // 基于准备好的dom，初始化echarts实例
  myChart.setOption(option, true);
}

function handleResize() {
  myChart.resize();
}

// 供父组件通过 ref 调用（原 Options API 方法默认公开，script setup 需显式暴露）
defineExpose({ wsFunc, handleSetVisitChart, handleResize });

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
</script>

<style scoped></style>
