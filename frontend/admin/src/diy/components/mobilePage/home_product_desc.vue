<template>
  <common_wrapper :config="configObj">
    <div class="product-desc-box">
      <div
        class="title"
        v-if="titleShow"
        :style="{
          color: titleColor,
          fontSize: titleSize + 'px',
          textAlign: titleAlign,
        }"
      >
        产品介绍
      </div>
      <div class="desc">此模块暂无内容设置，仅支持通用样式设置</div>
    </div>
  </common_wrapper>
</template>

<script>
// 模块级具名导出：DIY 编辑器新增组件时经聚合层取默认配置工厂
// （原 Vue2 时代通过组件选项 data().defaultConfig 暴露；timestamp 依赖实例 props，故为工厂函数）
export const getDefaultConfig = (num) => ({
  cname: '产品介绍',
  name: 'productDesc',
  timestamp: num,
  contentTitle: '内容设置',
  titleStyle: '标题样式',
  setUp: {
    tabVal: 0,
  },
  isShow: {
    title: '显示标题',
    tabVal: 0,
    tabList: [{ name: '显示' }, { name: '隐藏' }],
  },

  textPosition: {
    title: '对齐方式',
    val: 'center',
  },
  textColor: {
    title: '文字颜色',
    default: [{ item: '#333' }],
    color: [{ item: '#333' }],
  },
  fontSize: {
    title: '字体大小',
    val: 16,
    min: 12,
    max: 40,
  },
  titleCurrency: '通用样式',
  moduleColor: {
    title: '背景颜色',
    name: 'moduleColor',
    default: [{ item: '#fff' }],
    color: [{ item: '#fff' }],
  },
  bottomBgColor: {
    title: '底部背景',
    name: 'bottomBgColor',
    default: [{ item: '#fff' }],
    color: [{ item: '#fff' }],
  },
  marginConfig: {
    title: '外边距',
    val: 0,
    min: 0,
    max: 100,
    isAll: false,
    valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
  },
  paddingConfig: {
    title: '内边距',
    val: 10,
    min: 0,
    max: 100,
    isAll: false,
    valList: [{ val: 10 }, { val: 10 }, { val: 10 }, { val: 10 }],
  },
  componentBgConfig: {
    title: '组件背景',
    tabVal: 0,
    colorConfig: {
      title: '颜色设置',
      default: [{ item: '#fff' }],
      color: [{ item: '#fff' }],
    },
    imageConfig: {
      title: '图片设置',
      url: '',
    },
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
    tabList: [
      {
        name: '隐藏',
      },
      {
        name: '显示',
      },
    ],
    val: 0,
    colorConfig: {
      title: '阴影颜色',
      default: [
        {
          item: 'rgba(0,0,0,0.1)',
        },
      ],
      color: [
        {
          item: 'rgba(0,0,0,0.1)',
        },
      ],
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
  fillet: {
    title: '背景圆角',
    type: 0,
    list: [
      {
        val: '全部',
        icon: 'iconcaozuo-zhengti',
      },
      {
        val: '单个',
        icon: 'iconcaozuo-bianjiao',
      },
    ],
    valName: '圆角值',
    val: 0,
    min: 0,
    valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
  },
  borderRadius: '0',
  zIndexConfig: {
    title: '层级',
    val: 0,
    min: 0,
  },
});
</script>

<script setup>
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import { useMobildConfigStore } from '@diy/store/mobildConfig';

defineOptions({
  name: 'home_product_desc',
  cname: '产品介绍',
  configName: 'c_product_desc',
  icon: '#iconzujian-wenzhangliebiao',
  type: 3,
  defaultName: 'productDesc',
});

const props = defineProps({
  index: {
    type: null,
  },
  num: {
    type: null,
  },
});
// 实例级默认配置（timestamp 取当前实例 num）
const defaultConfig = getDefaultConfig(props.num);

const mobildConfigStore = useMobildConfigStore();

const titleShow = computed(() => {
  return configObj.value?.isShow?.tabVal == 0;
});

const titleColor = computed(() => {
  return configObj.value?.textColor?.color?.[0]?.item || '#333';
});

const titleSize = computed(() => {
  return configObj.value?.fontSize?.val || 16;
});

const titleAlign = computed(() => {
  return configObj.value?.textPosition.val || 'left';
});

const configObj = ref(null);
const pageData = ref({}); // watch(pageData) 引用，缺声明会在 setup 阶段抛 ReferenceError

// Vue2 原代码 watch 的 pageData 未在 data 中声明（死监听），转换时移除
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
    setConfig(mobildConfigStore.defaultArray[props.num]);
  });
});

function setConfig(data) {
  if (!data) return;
  if (data) {
    configObj.value = data;
  }
}
</script>

<style scoped>
.product-desc-box {
  text-align: center;
}
.title {
  font-size: 16px;
  font-weight: bold;
  color: #333;
}
.desc {
  font-size: 12px;
  color: #999;
  margin-top: 10px;
}
</style>
