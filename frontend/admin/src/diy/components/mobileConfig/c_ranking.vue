<template>
  <div class="mobile-config">
    <div v-for="(item, key) in rCom" :key="key">
      <component
        :is="item.components"
        :configObj="configObj"
        ref="childData"
        :configNme="item.configNme"
        :key="key"
        @getConfig="getConfig"
        :index="activeIndex"
        :num="item.num"
      ></component>
    </div>
    <rightBtn :activeIndex="activeIndex" :configObj="configObj"></rightBtn>
  </div>
</template>
<script setup>
import { nextTick, onMounted, ref, watch } from 'vue';
import toolCom from '@diy/components/mobileConfigRight/index.js';
import rightBtn from '@diy/components/rightBtn/index.vue';
import { useMobildConfigStore } from '@diy/store/mobildConfig';

defineOptions({ name: 'c_ranking', cname: '排行榜', componentsName: 'home_ranking' });
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
const configObj = ref({});
const rCom = ref([
  {
    components: toolCom.c_set_up,
    configNme: 'setUp',
  },
]);
const oneContent = [
  {
    components: toolCom.c_title,
    configNme: 'titleLeft',
  },
  {
    components: toolCom.c_radio,
    configNme: 'titleConfig',
  },
];
const oneContentImg = [
  {
    components: toolCom.c_upload_img,
    configNme: 'imgConfig',
  },
];
const oneContentText = [
  {
    components: toolCom.c_input_item,
    configNme: 'titleTxtConfig',
  },
];
const twoContent = [
  {
    components: toolCom.c_input_item,
    configNme: 'rightBntConfig',
  },
  {
    components: toolCom.c_title,
    configNme: 'titleGoods',
  },
  {
    components: toolCom.c_button_style,
    configNme: 'styleConfig',
  },
];
const oneStyle = [
  {
    components: toolCom.c_title,
    configNme: 'titleRight',
  },
];
const twoStyle = [
  {
    components: toolCom.c_radio,
    configNme: 'titleText',
  },
  {
    components: toolCom.c_bg_color,
    configNme: 'titleColor',
  },
  {
    components: toolCom.c_slider,
    configNme: 'titleNumber',
  },
];
const threeStyle = [
  {
    components: toolCom.c_bg_color,
    configNme: 'headerBntColor',
  },
  {
    components: toolCom.c_slider,
    configNme: 'bntNumber',
  },
];
const fourStyle = [
  {
    components: toolCom.c_title,
    configNme: 'titleRanking',
  },
  {
    components: toolCom.c_radio,
    configNme: 'toneConfig',
  },
];
const fiveStyle = [
  {
    components: toolCom.c_bg_color,
    configNme: 'classColor',
  },
  {
    components: toolCom.c_bg_color,
    configNme: 'goodsPriceColor',
  },
];
const currencyStyle = [
  {
    components: toolCom.c_title,
    configNme: 'titleCurrency',
  },
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
    configObj.value = patchConfig(value);
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
      getRComContent(arr, type);
    } else {
      getRComStyle(arr, type, type2);
    }
  },
  { deep: true },
);
watch(
  () => configObj.value?.titleConfig?.tabVal,
  (nVal, oVal) => {
    type = nVal;
    var arr = [rCom.value[0]];
    if (setUp == 0) {
      getRComContent(arr, nVal);
    } else {
      getRComStyle(arr, nVal, type2);
    }
  },
  { deep: true },
);
watch(
  () => configObj.value?.toneConfig?.tabVal,
  (nVal, oVal) => {
    type2 = nVal;
    var arr = [rCom.value[0]];
    if (setUp) {
      getRComStyle(arr, type, nVal);
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
      valList: [{ val: 0 }, { val: 0 }, { val: config.mbConfig ? config.mbConfig.val : 0 }, { val: 0 }],
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
function getRComContent(arr, type) {
  if (type == 0) {
    rCom.value = [...arr, ...oneContent, ...oneContentImg, ...twoContent];
  } else {
    rCom.value = [...arr, ...oneContent, ...oneContentText, ...twoContent];
  }
}
function getRComStyle(arr, type, type2) {
  if (type == 0) {
    if (type2 == 0) {
      rCom.value = [...arr, ...oneStyle, ...threeStyle, ...fourStyle, ...currencyStyle];
    } else {
      rCom.value = [...arr, ...oneStyle, ...threeStyle, ...fourStyle, ...fiveStyle, ...currencyStyle];
    }
  } else {
    if (type2 == 0) {
      rCom.value = [...arr, ...oneStyle, ...twoStyle, ...threeStyle, ...fourStyle, ...currencyStyle];
    } else {
      rCom.value = [...arr, ...oneStyle, ...twoStyle, ...threeStyle, ...fourStyle, ...fiveStyle, ...currencyStyle];
    }
  }
}
// 获取组件参数
function getConfig(data) {}
</script>

<style scoped></style>
