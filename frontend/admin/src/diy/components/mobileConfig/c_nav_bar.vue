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

defineOptions({ name: 'c_nav_bar', cname: '导航', componentsName: 'nav_bar' });
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
  // {
  //     components: toolCom.c_title,
  //     configNme: 'titleLeft'
  // },
  {
    components: toolCom.c_card_select,
    configNme: 'styleConfig',
  },
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
    configNme: 'stickyConfig',
  },
  {
    components: toolCom.c_title,
    configNme: 'titleTab',
  },
  {
    components: toolCom.c_tab_list,
    configNme: 'tabListConfig',
  },
];
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
    configNme: 'decorateColor',
  },
];
const twoStyle2 = [
  {
    components: toolCom.c_bg_color,
    configNme: 'decorateColor2',
  },
];
const threeStyle = [
  {
    components: toolCom.c_bg_color,
    configNme: 'textColor',
  },
];
const fourStyle = [
  {
    components: toolCom.c_bg_color,
    configNme: 'textColor2',
  },
];
const fiveStyle = [
  {
    components: toolCom.c_bg_color,
    configNme: 'textColor3',
  },
];
const currencyStyle = [
  {
    components: toolCom.c_common_style,
    configNme: 'commonStyle',
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
    var arr = [
      // {
      //     components: toolCom.c_title,
      //     configNme: 'titleLeft'
      // },
      {
        components: toolCom.c_card_select,
        configNme: 'styleConfig',
      },
      {
        components: toolCom.c_set_up,
        configNme: 'setUp',
      },
    ];
    if (nVal == 0) {
      rCom.value = arr.concat(oneContent);
    } else {
      if (type == 0) {
        if (type2 == 0) {
          rCom.value = [...arr, ...oneStyle, ...currencyStyle];
        } else {
          rCom.value = [...arr, ...oneStyle, ...twoStyle, ...threeStyle, ...currencyStyle];
        }
      } else if (type == 1) {
        if (type2 == 0) {
          rCom.value = [...arr, ...oneStyle, ...currencyStyle];
        } else {
          rCom.value = [...arr, ...oneStyle, ...twoStyle2, ...fourStyle, ...currencyStyle];
        }
      } else {
        if (type2 == 0) {
          rCom.value = [...arr, ...oneStyle, ...currencyStyle];
        } else {
          rCom.value = [...arr, ...oneStyle, ...twoStyle, ...fiveStyle, ...currencyStyle];
        }
      }
    }
  },
  { deep: true },
);
watch(
  () => configObj.value?.styleConfig?.tabVal,
  (nVal, oVal) => {
    type = nVal;
    var arr = [
      // {
      //     components: toolCom.c_title,
      //     configNme: 'titleLeft'
      // },
      {
        components: toolCom.c_card_select,
        configNme: 'styleConfig',
      },
      {
        components: toolCom.c_set_up,
        configNme: 'setUp',
      },
    ];
    if (setUp) {
      if (nVal == 0) {
        if (type2 == 0) {
          rCom.value = [...arr, ...oneStyle, ...currencyStyle];
        } else {
          rCom.value = [...arr, ...oneStyle, ...twoStyle, ...threeStyle, ...currencyStyle];
        }
      } else if (nVal == 1) {
        if (type2 == 0) {
          rCom.value = [...arr, ...oneStyle, ...currencyStyle];
        } else {
          rCom.value = [...arr, ...oneStyle, ...twoStyle2, ...fourStyle, ...currencyStyle];
        }
      } else {
        if (type2 == 0) {
          rCom.value = [...arr, ...oneStyle, ...currencyStyle];
        } else {
          rCom.value = [...arr, ...oneStyle, ...twoStyle, ...fiveStyle, ...currencyStyle];
        }
      }
    }
  },
  { deep: true },
);
watch(
  () => configObj.value?.toneConfig?.tabVal,
  (nVal, oVal) => {
    type2 = nVal;
    var arr = [
      // {
      //     components: toolCom.c_title,
      //     configNme: 'titleLeft'
      // },
      {
        components: toolCom.c_card_select,
        configNme: 'styleConfig',
      },
      {
        components: toolCom.c_set_up,
        configNme: 'setUp',
      },
    ];
    if (setUp) {
      if (type == 0) {
        if (nVal == 0) {
          rCom.value = [...arr, ...oneStyle, ...currencyStyle];
        } else {
          rCom.value = [...arr, ...oneStyle, ...twoStyle, ...threeStyle, ...currencyStyle];
        }
      } else if (type == 1) {
        if (nVal == 0) {
          rCom.value = [...arr, ...oneStyle, ...currencyStyle];
        } else {
          rCom.value = [...arr, ...oneStyle, ...twoStyle2, ...fourStyle, ...currencyStyle];
        }
      } else {
        if (nVal == 0) {
          rCom.value = [...arr, ...oneStyle, ...currencyStyle];
        } else {
          rCom.value = [...arr, ...oneStyle, ...twoStyle, ...fiveStyle, ...currencyStyle];
        }
      }
    }
  },
  { deep: true },
);
onMounted(() => {
  nextTick(() => {
    let value = JSON.parse(JSON.stringify(mobildConfigStore.defaultArray[props.num]));
    configObj.value = value;
  });
});
function patchConfig(data) {
  if (!data) return data;
  if (!data.paddingConfig) {
    data['paddingConfig'] = {
      isAll: false,
      title: '内边距',
      val: 0,
      min: 0,
      max: 100,
      valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
    };
    if (data.topConfig) data.paddingConfig.valList[0].val = data.topConfig.val;
    if (data.prConfig) {
      data.paddingConfig.valList[1].val = data.prConfig.val;
      data.paddingConfig.valList[3].val = data.prConfig.val;
    }
    if (data.bottomConfig) data.paddingConfig.valList[2].val = data.bottomConfig.val;
  }
  if (!data.marginConfig) {
    data['marginConfig'] = {
      isAll: false,
      title: '外边距',
      val: 0,
      min: 0,
      max: 100,
      valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
    };
    if (data.mbConfig) data.marginConfig.valList[0].val = data.mbConfig.val;
  }
  return data;
}
// 获取组件参数
function getConfig(data) {}
</script>

<style scoped></style>
