<template>
  <common_wrapper :config="configObj">
    <div class="time-card-box" v-if="configObj">
      <div
        class="title"
        v-if="titleShow"
        :style="{ color: titleColor, fontSize: titleSize + 'px', textAlign: titleAlign }"
      >
        时间线
      </div>
      <!-- mock 时间线条目 -->
      <div class="mock-timeline">
        <div class="mock-tl-item">
          <div class="mock-tl-dot"></div>
          <div class="mock-tl-content">
            <div class="mock-tl-time">09:00 - 12:00</div>
            <div class="mock-tl-label">上午场</div>
          </div>
        </div>
        <div class="mock-tl-item">
          <div class="mock-tl-dot muted"></div>
          <div class="mock-tl-content">
            <div class="mock-tl-time">14:00 - 17:00</div>
            <div class="mock-tl-label">下午场</div>
          </div>
        </div>
        <div class="mock-tl-item">
          <div class="mock-tl-dot muted"></div>
          <div class="mock-tl-content">
            <div class="mock-tl-time">19:00 - 21:00</div>
            <div class="mock-tl-label">夜场</div>
          </div>
        </div>
      </div>
    </div>
  </common_wrapper>
</template>

<script>
// 模块级具名导出：DIY 编辑器新增组件时经聚合层取默认配置工厂
// （原 Vue2 时代通过组件选项 data().defaultConfig 暴露；timestamp 依赖实例 props，故为工厂函数）
export const getDefaultConfig = (num) => ({
  cname: '时间线',
  name: 'timeCard',
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
  name: 'home_time_card',
  cname: '时间线',
  configName: 'c_time_card',
  icon: '#iconzujian-wenzhangliebiao',
  type: 3,
  defaultName: 'timeCard',
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
.time-card-box {
  padding: 4px 0;
}
.title {
  font-size: 16px;
  font-weight: bold;
  color: #333;
  margin-bottom: 10px;
}
.mock-timeline {
  display: flex;
  flex-direction: column;
  gap: 10px;
  padding-left: 4px;
}
.mock-tl-item {
  display: flex;
  align-items: center;
  gap: 10px;
}
.mock-tl-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #e93323;
  flex-shrink: 0;
}
.mock-tl-dot.muted {
  background: #ddd;
}
.mock-tl-content {
  display: flex;
  align-items: center;
  gap: 8px;
}
.mock-tl-time {
  font-size: 12px;
  color: #333;
  font-weight: 500;
}
.mock-tl-label {
  font-size: 11px;
  color: #999;
  background: #f7f8fa;
  padding: 1px 6px;
  border-radius: 3px;
}
</style>
