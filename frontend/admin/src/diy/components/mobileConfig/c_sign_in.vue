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

defineOptions({ name: 'c_sign_in', cname: '签到', componentsName: 'sign_in' });
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
// 组件参数配置
const option = {
  submitBtn: false,
};
const configObj = ref({});
// 配置对象
const rCom = ref([
  {
    components: toolCom.c_card_select,
    configNme: 'styleConfig',
  },
]);
// 当前页面组件
const oneStyle = [
  {
    components: toolCom.c_title,
    configNme: 'titleRight',
  },
  {
    components: toolCom.c_radio,
    configNme: 'toneConfig',
  },
];
const twoStyle = [
  {
    components: toolCom.c_bg_color,
    configNme: 'bntBgColor',
  },
  {
    components: toolCom.c_bg_color,
    configNme: 'bntTxtColor',
  },
];
const twoStyle2 = [
  {
    components: toolCom.c_bg_color,
    configNme: 'labelBgColor',
  },
  {
    components: toolCom.c_bg_color,
    configNme: 'labelTxtColor',
  },
];
const titleStyle = [];
const moduleStyle = [];
const moduleStyle2 = [
  // {
  //   components: toolCom.c_bg_color,
  //   configNme: 'moduleColor2',
  // },
];
const currencyStyle = [
  {
    components: toolCom.c_common_style,
    configNme: 'c_common_style',
  },
];
let type = 0;
let type2 = 0;
watch(
  () => props.num,
  (nVal) => {
    // debugger;
    let value = JSON.parse(JSON.stringify(mobildConfigStore.defaultArray[nVal]));
    configObj.value = patchConfig(value);
    refreshStyleComponents();
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
  () => configObj.value?.styleConfig?.tabVal,
  (nVal, oVal) => {
    type2 = nVal;
    var arr = getBaseComponents();
    getRComStyle(arr, type, nVal);
  },
  { deep: true },
);
watch(
  () => configObj.value?.toneConfig?.tabVal,
  (nVal, oVal) => {
    type = nVal;
    var arr = getBaseComponents();
    getRComStyle(arr, nVal, type2);
  },
  { deep: true },
);
onMounted(() => {
  nextTick(() => {
    let value = JSON.parse(JSON.stringify(mobildConfigStore.defaultArray[props.num]));
    configObj.value = patchConfig(value);
    refreshStyleComponents();
  });
});
function getBaseComponents() {
  return [
    {
      components: toolCom.c_card_select,
      configNme: 'styleConfig',
    },
  ];
}
function refreshStyleComponents() {
  if (!configObj.value.styleConfig || !configObj.value.toneConfig) return;
  type = configObj.value.toneConfig.tabVal;
  type2 = configObj.value.styleConfig.tabVal;
  getRComStyle(getBaseComponents(), type, type2);
}
function patchConfig(config) {
  if (!config.styleConfig.tabList) {
    config.styleConfig['tabList'] = [{ name: '样式一' }, { name: '样式二' }];
  }
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
      valList: [{ val: config.mbConfig ? config.mbConfig.val : 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
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
function getRComStyle(arr, type, type2) {
  if (type2 == 0) {
    if (type == 0) {
      rCom.value = [...arr, ...oneStyle, ...titleStyle, ...moduleStyle2, ...currencyStyle];
    } else {
      rCom.value = [...arr, ...oneStyle, ...twoStyle, ...titleStyle, ...moduleStyle2, ...currencyStyle];
    }
  } else {
    if (type == 0) {
      rCom.value = [...arr, ...oneStyle, ...titleStyle, ...moduleStyle, ...currencyStyle];
    } else {
      rCom.value = [...arr, ...oneStyle, ...twoStyle, ...twoStyle2, ...titleStyle, ...moduleStyle, ...currencyStyle];
    }
  }
}
function getConfig(data) {}
</script>

<style scoped></style>
