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
import { ElMessage } from 'element-plus';

defineOptions({ name: 'c_short_video', componentsName: 'home_short_video' });
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
    configNme: 'styleConfig',
  },
  {
    components: toolCom.c_title,
    configNme: 'titleHead',
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
    configNme: 'titleContent',
  },
  {
    components: toolCom.c_slider,
    configNme: 'numberConfig',
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
  {
    components: toolCom.c_title,
    configNme: 'titleVideoStyle',
  },
];
const videoSpaceStyle = [
  {
    components: toolCom.c_slider,
    configNme: 'videoSpace',
  },
];
const videoSpaceStyle2 = [
  {
    components: toolCom.c_slider,
    configNme: 'videoSpace2',
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
    configObj.value = mobildConfigStore.defaultArray[nVal];
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
      getRComContent(arr, type2);
    } else {
      getRComStyle(arr, type, type2);
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
      getRComContent(arr, type2);
    } else {
      getRComStyle(arr, nVal, type2);
    }
  },
);
watch(
  () => configObj.value?.titleConfig?.tabVal,
  (nVal, oVal) => {
    type2 = nVal;
    var arr = [rCom.value[0]];
    if (setUp == 0) {
      getRComContent(arr, nVal);
    } else {
      getRComStyle(arr, type, nVal);
    }
  },
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
function getRComContent(arr, type2) {
  if (type2 == 0) {
    rCom.value = [...arr, ...oneContent, ...oneContentImg, ...twoContent];
  } else {
    rCom.value = [...arr, ...oneContent, ...oneContentText, ...twoContent];
  }
}
function getRComStyle(arr, type, type2) {
  if (type == 0) {
    if (type2 == 0) {
      rCom.value = [...arr, ...oneStyle, ...threeStyle, ...videoSpaceStyle, ...currencyStyle];
    } else {
      rCom.value = [...arr, ...oneStyle, ...twoStyle, ...threeStyle, ...videoSpaceStyle, ...currencyStyle];
    }
  } else {
    if (type2 == 0) {
      rCom.value = [...arr, ...oneStyle, ...threeStyle, ...videoSpaceStyle2, ...currencyStyle];
    } else {
      rCom.value = [...arr, ...oneStyle, ...twoStyle, ...threeStyle, ...videoSpaceStyle2, ...currencyStyle];
    }
  }
}
function getVideoList(limit) {
  videoList({
    page: 1,
    limit: limit,
  })
    .then((res) => {
      configObj.value.videoList = res.data.list;
    })
    .catch((err) => {
      ElMessage.error(err.msg);
    });
}
// 获取组件参数
function getConfig(data) {
  if (data.name == 'radio') {
    return;
  }
  // getVideoList(data.numVal);
}
function handleSubmit(name) {
  let obj = {};
  obj.activeIndex = props.activeIndex;
  obj.data = configObj.value;
  mobildConfigStore.UPDATEARR(obj);
}
</script>

<style scoped></style>
