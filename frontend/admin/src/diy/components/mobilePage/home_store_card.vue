<template>
  <common_wrapper :config="configObj">
    <div class="store-card-box" v-if="configObj">
      <div
        class="title"
        v-if="titleShow"
        :style="{ color: titleColor, fontSize: titleSize + 'px', textAlign: titleAlign }"
      >
        门店/场馆卡
      </div>
      <!-- mock 门店信息 -->
      <div class="mock-store">
        <div class="mock-store__header">
          <div class="mock-store__icon"></div>
          <div class="mock-store__info">
            <div class="mock-store__name">阳光水族馆（总店）</div>
            <div class="mock-store__addr">上海市浦东新区陆家嘴环路1388号</div>
          </div>
        </div>
        <div class="mock-store__meta">
          <span class="mock-tag">距您 2.3km</span>
          <span class="mock-tag">营业中</span>
        </div>
      </div>
    </div>
  </common_wrapper>
</template>

<script>
// 模块级具名导出：DIY 编辑器新增组件时经聚合层取默认配置工厂
// （原 Vue2 时代通过组件选项 data().defaultConfig 暴露；timestamp 依赖实例 props，故为工厂函数）
export const getDefaultConfig = (num) => ({
  cname: '门店/场馆卡',
  name: 'storeCard',
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
      default: [{ item: 'transparent' }],
      color: [{ item: 'transparent' }],
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
  name: 'home_store_card',
  cname: '门店',
  configName: 'c_store_card',
  icon: '#iconzujian-shangpinxinxi',
  type: 3,
  defaultName: 'storeCard',
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
.store-card-box {
  padding: 4px 0;
}
.title {
  font-size: 16px;
  font-weight: bold;
  color: #333;
  margin-bottom: 10px;
}
.mock-store {
  padding: 8px;
  background: #f7f8fa;
  border-radius: 6px;
}
.mock-store__header {
  display: flex;
  align-items: center;
  gap: 10px;
}
.mock-store__icon {
  width: 36px;
  height: 36px;
  background: #e93323;
  border-radius: 6px;
  flex-shrink: 0;
  opacity: 0.8;
}
.mock-store__info {
  flex: 1;
  min-width: 0;
}
.mock-store__name {
  font-size: 13px;
  font-weight: 500;
  color: #333;
}
.mock-store__addr {
  font-size: 11px;
  color: #999;
  margin-top: 2px;
}
.mock-store__meta {
  display: flex;
  gap: 6px;
  margin-top: 8px;
}
.mock-tag {
  font-size: 10px;
  color: #e93323;
  background: rgba(233, 51, 35, 0.08);
  padding: 2px 6px;
  border-radius: 3px;
}
</style>
