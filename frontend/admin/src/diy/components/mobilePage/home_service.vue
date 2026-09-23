<template>
  <common_wrapper :config="configObj">
    <div class="service-box" :class="positions ? '' : 'on'">
      <div class="img-box">
        <img :src="imgUrl" alt="" v-if="imgUrl" />
        <div class="empty-box on" v-else>
          <img src="@diy/assets/images/shan.png" />
        </div>
      </div>
    </div>
  </common_wrapper>
</template>

<script>
// 模块级具名导出：DIY 编辑器新增组件时经聚合层取默认配置工厂
// （原 Vue2 时代通过组件选项 data().defaultConfig 暴露；timestamp 依赖实例 props，故为工厂函数）
export const getDefaultConfig = (num) => ({
  cname: '悬浮按钮',
  name: 'customerService',
  timestamp: num,
  isHide: false,
  setUp: {
    tabVal: 0,
  },
  titleLeft: '按钮设置',
  titleRight: '位置设置',
  buttonConfig: {
    title: '按钮跳转',
    tabVal: 0,
    tabList: [
      {
        name: '页面链接',
      },
      {
        name: '客服入口',
      },
    ],
  },
  locationConfig: {
    title: '展示位置',
    tabVal: 1,
    tabList: [
      {
        name: '左',
      },
      {
        name: '右',
      },
    ],
  },
  logoConfig: {
    title: '建议：展示上传100*100px；',
    url: '',
    link: '',
  },
  zIndexConfig: {
    title: '组件上浮',
    val: 0,
    min: 0,
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
  shadowConfig: {
    title: '阴影设置',
    tabVal: 0,
    tabList: [{ name: '隐藏' }, { name: '显示' }],
    val: 0, // 0: Hide, 1: Show
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
  // 页面间距
  paddingConfig: {
    title: '内边距',
    val: 0,
    min: 0,
    isAll: false,
    valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
  },
  marginConfig: {
    title: '外边距',
    val: 0,
    min: 0,
    isAll: false,
    valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
  },
});
</script>

<script setup>
import { ref, watch, onMounted, nextTick } from 'vue';
import { useMobildConfigStore } from '@diy/store/mobildConfig';

defineOptions({
  name: 'home_service',
  cname: '悬浮按钮',
  configName: 'c_home_service',
  icon: '#iconzujian-xuanfuanniu',
  type: 2,
  // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'customerService',
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

const imgUrl = ref('');
const pageData = ref({});
const mTop = ref(0);
const positions = ref(1);
//展示位置
const configObj = ref(null);

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
    const data = mobildConfigStore.defaultArray[nVal];
    setConfig(data);
  },
  { deep: true },
);
watch(
  () => mobildConfigStore.defaultArray,
  (nVal, oVal) => {
    const data = mobildConfigStore.defaultArray[props.num];
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
  if (configObj.value.componentBgConfig !== undefined) {
    delete configObj.value['componentBgConfig'];
  }
  for (let key in defaultConfig) {
    if (configObj.value[key] === undefined) {
      configObj.value[key] = defaultConfig[key];
    }
  }
  imgUrl.value = data.logoConfig.url;
  positions.value = data.locationConfig.tabVal;
}
</script>

<style scoped lang="scss">
.service-box {
  width: 100%;
  display: flex;
  justify-content: flex-end;
  padding-right: 10px;
  &.on {
    justify-content: flex-start;
    padding-left: 10px;
  }
  .img-box {
    width: 43px;
    height: 43px;
    img {
      width: 100%;
      height: 100%;
      border-radius: 50%;
    }
    .empty-box {
      border-radius: 50%;
      background: #f3f9ff;
      img {
        width: 26px;
        height: 20px;
      }
      .iconfont {
        font-size: 20px;
      }
    }
  }
}
</style>
