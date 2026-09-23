<template>
  <div class="mobile-config">
    <!-- 原 iView <Form> 组件迁移后不存在，回退渲染为原生 form；显式改原生标签消除控制台警告 -->
    <form>
      <div v-for="(item, key) in rCom" :key="key">
        <component
          :is="item.components"
          :configObj="configObj"
          ref="childData"
          :configNme="item.configNme"
          :key="key"
          :index="activeIndex"
          :num="item.num"
        ></component>
      </div>
      <rightBtn :activeIndex="activeIndex" :configObj="configObj"></rightBtn>
    </form>
  </div>
</template>
<script setup>
import { nextTick, onMounted, ref, watch } from 'vue';
import toolCom from '@diy/components/mobileConfigRight/index.js';
import rightBtn from '@diy/components/rightBtn/index.vue';
import { useMobildConfigStore } from '@diy/store/mobildConfig';

defineOptions({ name: 'c_search_box', cname: '搜索框', componentsName: 'search_box' });
const props = defineProps({
  activeIndex: {
    type: null,
  },
  num: {
    type: null,
  },
  index: {
    type: null,
  },
});
const mobildConfigStore = useMobildConfigStore();
const hotIndex = 1;
const configObj = ref({});
// 配置对象
const rCom = ref([
  {
    components: toolCom.c_set_up,
    configNme: 'setUp',
  },
]);
// 当前页面组件
const oneContent = [
  {
    components: toolCom.c_title,
    configNme: 'titleLeft',
  },
  {
    components: toolCom.c_radio,
    configNme: 'styleConfig',
  },
];
const oneContentType = [
  {
    components: toolCom.c_radio,
    configNme: 'styleTypeConfig',
  },
];
const oneContentFix = [
  {
    components: toolCom.c_radio,
    configNme: 'fixConfig',
  },
];
const twoContent = [
  {
    components: toolCom.c_upload_img,
    configNme: 'logoConfig',
  },
];
const threeContent = [
  {
    components: toolCom.c_input_item,
    configNme: 'titleConfig',
  },
  {
    components: toolCom.c_input_item,
    configNme: 'linkConfig',
  },
];
const rComContent = [
  {
    components: toolCom.c_title,
    configNme: 'titleSearch',
  },
  {
    components: toolCom.c_input_item,
    configNme: 'tipConfig',
  },
  {
    components: toolCom.c_title,
    configNme: 'titleHotWords',
  },
  {
    components: toolCom.c_hot_word,
    configNme: 'hotWords',
  },
  {
    components: toolCom.c_input_number,
    configNme: 'numConfig',
  },
];
const oneStyle = [
  {
    components: toolCom.c_title,
    configNme: 'titleRight',
  },
  {
    components: toolCom.c_bg_color,
    configNme: 'tipColor',
  },
  {
    components: toolCom.c_bg_color,
    configNme: 'hotWordsColor',
  },
];
const twoStyle = [
  {
    components: toolCom.c_title,
    configNme: 'titleTxt',
  },
  {
    components: toolCom.c_radio,
    configNme: 'txtFixConfig',
  },
];
const twoStyle1 = [
  {
    components: toolCom.c_radio,
    configNme: 'txtStyleConfig',
  },
  {
    components: toolCom.c_bg_color,
    configNme: 'txtColor',
  },
  {
    components: toolCom.c_slider,
    configNme: 'txtSize',
  },
];
const currencyStyle = [
  {
    components: toolCom.c_common_style,
    configNme: 'c_common_style',
  },
];
let setUp = 0;
let type = 0;
let type2 = 0;
watch(
  () => props.num,
  (nVal) => {
    // debugger;
    let value = JSON.parse(JSON.stringify(mobildConfigStore.defaultArray[nVal]));
    configObj.value = value;
  },
);
watch(
  configObj,
  (nVal, oVal) => {
    mobildConfigStore.UPDATEARR({ num: props.num, val: nVal });
  },
  { deep: true },
);
watch(
  () => configObj.value?.setUp?.tabVal,
  (nVal, oVal) => {
    setUp = nVal;
    var arr = [rCom.value[0]];
    if (nVal == 0) {
      getRComContent(arr);
    } else {
      getRComStyle(arr);
    }
  },
  { deep: true },
);
watch(
  () => configObj.value?.styleConfig?.tabVal,
  (nVal, oVal) => {
    type = nVal;
    var arr = [rCom.value[0]];
    if (setUp == 0) {
      getRComContent(arr);
    } else {
      getRComStyle(arr);
    }
  },
  { deep: true },
);
watch(
  () => configObj.value?.styleTypeConfig?.tabVal,
  (nVal, oVal) => {
    type2 = nVal;
    var arr = [rCom.value[0]];
    if (setUp == 0) {
      getRComContent(arr);
    } else {
      getRComStyle(arr);
    }
  },
  { deep: true },
);
onMounted(() => {
  nextTick(() => {
    let value = JSON.parse(JSON.stringify(mobildConfigStore.defaultArray[props.num]));
    configObj.value = patchConfig(value);
  });
});
function patchConfig(config) {
  if (!config.paddingConfig) {
    config.paddingConfig = {
      title: '内边距',
      val: 0,
      min: 0,
      max: 100,
      isAll: false,
      valList: [
        { val: config.topConfig ? config.topConfig.val : 0 },
        { val: config.prConfig ? config.prConfig.val : 0 },
        { val: config.bottomConfig ? config.bottomConfig.val : 0 },
        { val: config.prConfig ? config.prConfig.val : 0 },
      ],
    };
  }
  if (!config.marginConfig) {
    config.marginConfig = {
      title: '外边距',
      val: 0,
      min: 0,
      max: 100,
      isAll: false,
      valList: [{ val: config.mbConfig ? config.mbConfig.val : 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
    };
  }
  if (!config.componentBgConfig) {
    config.componentBgConfig = {
      title: '背景设置',
      tabVal: 0,
      tabList: [{ name: '颜色' }, { name: '图片' }],
      colorConfig: {
        title: '背景颜色',
        default: [{ item: '#fff' }, { item: '#fff' }],
        color: [{ item: '#fff' }, { item: '#fff' }],
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
    };
  }
  if (!config.borderConfig) {
    config.borderConfig = {
      title: '边框设置',
      tabVal: 0,
      tabList: [{ name: '隐藏' }, { name: '显示' }],
      val: 0,
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
        max: 20,
      },
      colorConfig: {
        title: '边框颜色',
        default: [{ item: '#e5e5e5' }],
        color: [{ item: '#e5e5e5' }],
      },
    };
  }
  if (!config.shadowConfig) {
    config.shadowConfig = {
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
        max: 50,
      },
      yConfig: {
        title: 'Y轴偏移',
        val: 0,
        min: -50,
        max: 50,
      },
      blurConfig: {
        title: '模糊半径',
        val: 10,
        min: 0,
        max: 50,
      },
      spreadConfig: {
        title: '扩展半径',
        val: 0,
        min: -50,
        max: 50,
      },
    };
  }
  if (!config.c_common_style) {
    config.c_common_style = {
      color: 'rgba(255,255,255,1)',
      color2: 'rgba(255,255,255,1)',
      lr: 0,
      type: 0,
    };
  }
  return config;
}
function getRComContent(arr) {
  if (type == 1) {
    // rCom.value = [...arr, ...oneContent, ...threeContent];
    rCom.value = [...arr, ...oneContent, ...threeContent, ...oneContentFix];
  } else if (type == 2) {
  } else {
    if (type2 == 0) {
      rCom.value = [...arr, ...oneContent, ...oneContentType, ...threeContent, ...rComContent];
    } else if (type2 == 1) {
      rCom.value = [...arr, ...oneContent, ...oneContentType, ...twoContent, ...rComContent];
    } else {
      rCom.value = [...arr, ...oneContent, ...oneContentType, ...oneContentFix, ...rComContent];
    }
  }
}
function getRComStyle(arr) {
  if (type == 0) {
    if (type2 == 2) {
      rCom.value = [...arr, ...oneStyle, ...twoStyle, ...currencyStyle];
    } else {
      rCom.value = [...arr, ...oneStyle, ...twoStyle, ...twoStyle1, ...currencyStyle];
    }
  } else {
    rCom.value = [...arr, ...twoStyle, ...twoStyle1, ...currencyStyle];
  }
}
</script>

<style scoped lang="scss">
.title-tips {
  padding-bottom: 10px;
  font-size: 14px;
  color: #333;
  span {
    margin-right: 14px;
    color: #999;
  }
}
</style>
