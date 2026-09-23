<template>
  <common_wrapper :config="configObj">
    <div
      class="box"
      :style="{
        borderBottomColor: lineColor,
        borderBottomStyle: style,
        borderBottomWidth: `${configObj && configObj.heightConfig.val}px`,
      }"
    ></div>
  </common_wrapper>
</template>

<script>
// 模块级具名导出：DIY 编辑器新增组件时经聚合层取默认配置工厂
// （原 Vue2 时代通过组件选项 data().defaultConfig 暴露；timestamp 依赖实例 props，故为工厂函数）
export const getDefaultConfig = (num) => ({
  cname: '辅助线',
  name: 'guide',
  timestamp: num,
  isHide: false,
  setUp: {
    tabVal: 0,
  },
  titleLeft: '展示设置',
  titleRight: '线条样式',
  titleCurrent: '通用样式',
  zIndexConfig: {
    title: '组件上浮',
    val: 0,
    min: 0,
  },
  lineColor: {
    title: '线条颜色',
    default: [
      {
        item: '#f5f5f5',
      },
    ],
    color: [
      {
        item: '#f5f5f5',
      },
    ],
  },
  lineBgColor: {
    title: '底部背景',
    default: [
      {
        item: '#E93323',
      },
    ],
    color: [
      {
        item: '#E93323',
      },
    ],
  },
  lineStyle: {
    title: '选择样式',
    tabVal: 1,
    tabList: [
      {
        name: '虚线',
        style: 'dashed',
      },
      {
        name: '实线',
        style: 'solid',
      },
      {
        name: '点状线',
        style: 'dotted',
      },
    ],
  },
  paddingConfig: {
    title: '内边距',
    val: 0,
    min: 0,
    max: 100,
    valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
  },
  borderConfig: {
    title: '边框设置',
    tabVal: 0,
    tabList: [{ name: '隐藏' }, { name: '显示' }],
    val: 0, // 0: Hide, 1: Show
    styleConfig: {
      title: '边框样式',
      tabVal: 0,
      tabList: [
        { name: '实线', style: 'solid' },
        { name: '虚线', style: 'dashed' },
        { name: '点状', style: 'dotted' },
      ],
    },
    widthConfig: {
      title: '边框粗细',
      val: 1,
      min: 1,
    },
    colorConfig: {
      title: '边框颜色',
      default: [{ item: '#e5e5e5' }],
      color: [{ item: '#e5e5e5' }],
    },
  },
  heightConfig: {
    title: '线条高度',
    val: 10,
    min: 1,
  },
  shadowConfig: {
    title: '阴影设置',
    tabVal: 0,
    tabList: [{ name: '隐藏' }, { name: '显示' }],
    val: 0,
    colorConfig: {
      title: '阴影颜色',
      default: [{ item: 'rgba(0,0,0,0.1)' }],
      color: [{ item: 'rgba(0,0,0,0.1)' }],
    },
    xConfig: {
      title: 'X轴偏移',
      val: 0,
      min: -50,
    },
    yConfig: {
      title: 'Y轴偏移',
      val: 0,
      min: -50,
    },
    blurConfig: {
      title: '模糊半径',
      val: 10,
      min: 0,
    },
    spreadConfig: {
      title: '扩展半径',
      val: 0,
      min: -50,
    },
  },
  componentBgConfig: {
    title: '背景设置',
    tabVal: 0,
    tabList: [{ name: '颜色' }, { name: '图片' }],
    colorConfig: {
      title: '背景颜色',
      default: [{ item: '#F5F5F5' }, { item: '#F5F5F5' }],
      color: [{ item: '#F5F5F5' }, { item: '#F5F5F5' }],
    },
    colorDirection: {
      title: '渐变方向',
      tabVal: 0,
      tabList: [{ name: '横向' }, { name: '纵向' }, { name: '左斜' }, { name: '右斜' }],
    },
    imageConfig: {
      header: '背景图片',
      title: '',
      name: '上传图片',
      type: 'code',
      url: '',
      info: '建议尺寸：750px * 400px',
    },
  },
  marginConfig: {
    title: '外边距',
    val: 0,
    min: 0,
    max: 100,
    valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
  },
});
</script>

<script setup>
import { ref, watch, onMounted, nextTick } from 'vue';
import { useMobildConfigStore } from '@diy/store/mobildConfig';

defineOptions({
  name: 'z_auxiliary_line',
  cname: '辅助线',
  configName: 'c_auxiliary_line',
  icon: '#iconzujian-fuzhuxian',
  type: 2,
  // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'guide',
});

const props = defineProps({
  index: {
    type: null,
    default: -1,
  },
  num: {
    type: null,
  },
});
// 实例级默认配置（timestamp 取当前实例 num）
const defaultConfig = getDefaultConfig(props.num);

const mobildConfigStore = useMobildConfigStore();

// 默认初始化数据禁止修改

const configObj = ref(null);
const bgColor = ref('');
const confObj = ref({});
const pageData = ref({});
const edge = ref('');
const udEdge = ref('');
const topConfig = ref('');
const bottomConfig = ref('');
const style = ref('');
const lineColor = ref('');
const paddingConfig = ref({
  title: '内边距',
  val: 0,
  min: 0,
  max: 100,
  valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
});
const marginConfig = ref({
  title: '外边距',
  val: 0,
  min: 0,
  max: 100,
  valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
});

watch(
  pageData,
  (nVal, oVal) => {
    setConfig(nVal);
  },
  { deep: true },
);
watch(
  () => props.num,
  (nVal, oVal) => {
    let data = mobildConfigStore.defaultArray[nVal];
    setConfig(data);
  },
  { deep: true },
);
watch(
  () => mobildConfigStore.defaultArray,
  (nVal, oVal) => {
    let data = mobildConfigStore.defaultArray[props.num];
    setConfig(data);
  },
  { deep: true },
);

onMounted(() => {
  nextTick(() => {
    pageData.value = mobildConfigStore.defaultArray[props.num];
    setConfig(pageData.value);
  });
});

function setConfig(data) {
  if (!data) return;
  configObj.value = data;
  configObj.value['bottomBgColor'] = data.lineBgColor;
  let styleType = data.lineStyle.tabVal;
  bgColor.value = data.lineBgColor.color[0].item;
  lineColor.value = data.lineColor.color[0].item;
  style.value = data.lineStyle.tabList[styleType].style;

  for (let key in defaultConfig) {
    if (configObj.value[key] === undefined) {
      configObj.value[key] = defaultConfig[key];
    }
  }

  if (!data.paddingConfig) {
    data.paddingConfig = {
      isAll: false,
      valList: [
        { val: data.topConfig ? data.topConfig.val : 0 },
        { val: data.lrEdge ? data.lrEdge.val : 0 },
        { val: data.bottomConfig ? data.bottomConfig.val : 0 },
        { val: data.lrEdge ? data.lrEdge.val : 0 },
      ],
    };
  }

  if (!data.marginConfig) {
    data.marginConfig = {
      isAll: false,
      valList: [{ val: 0 }, { val: 0 }, { val: data.mbConfig ? data.mbConfig.val : 0 }, { val: 0 }],
    };
  }
}
</script>

<style scoped lang="scss">
.mobile-page {
  padding: 7px 0;
  display: inline-block;
  width: -webkit-fill-available;
}
</style>
