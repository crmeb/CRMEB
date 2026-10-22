<template>
  <div>
    <div :id="echartsId" style="height: 300px"></div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick, getCurrentInstance } from 'vue';
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

// 组件实例 uid 保证同一页面多个图表容器 id 唯一，避免随机数碰撞导致图表渲染到错误节点
const instance = getCurrentInstance();
const echartsId = computed(() => `echarts-${instance ? instance.uid : Date.now()}`);

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
  if (myChart) {
    myChart.resize();
  }
}

function handleSetVisitChart() {
  const dom = document.getElementById(echartsId.value);
  // 数据 watcher 可能在挂载前触发，容器未就绪时跳过，挂载后统一初始化
  if (!dom) {
    return;
  }
  // 复用实例，不能每次数据变化都重复 init（会产生 “chart instance already initialized” 并导致图表空白）
  if (!myChart) {
    myChart = echarts.init(dom);
  }
  let option = null;
  if (props.echartsTitle === 'circle') {
    option = {
      tooltip: {
        trigger: 'item',
        formatter: '{a} <br/>{b} : {c} ({d}%)',
      },
      legend: {
        orient: 'vertical',
        right: 12,
        top: 'middle',
        itemWidth: 10,
        itemHeight: 10,
        itemGap: 12,
        textStyle: {
          color: '#606266',
          fontSize: 12,
        },
        data: infoLists.value.bing_xdata || [],
      },
      series: [
        {
          name: '访问来源',
          type: 'pie',
          radius: '58%',
          center: ['42%', '50%'],
          avoidLabelOverlap: true,
          minAngle: 3,
          label: {
            show: true,
            // 0 值扇区不显示标签，避免全部堆在圆心附近
            formatter: (params) => (Number(params.value) > 0 ? `${params.name}\n${params.percent}%` : ''),
            color: '#606266',
            fontSize: 12,
            lineHeight: 18,
          },
          labelLine: {
            length: 12,
            length2: 8,
            lineStyle: { color: '#c0c4cc' },
          },
          // 0 值分类不绘制扇区，避免零角度扇区的标签/引导线堆积在圆心
          data: (infoLists.value.bing_data || []).filter((item) => Number(item.value) > 0),
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
