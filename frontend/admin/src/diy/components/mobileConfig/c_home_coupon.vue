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

defineOptions({ name: 'c_home_coupon', componentsName: 'home_coupon' });
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
    components: toolCom.c_card_select,
    configNme: 'styleConfig',
  },
  {
    components: toolCom.c_set_up,
    configNme: 'setUp',
  },
]);
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
    configNme: 'couponMoneyColor',
  },
];
const reduceTxtStyle = [
  {
    components: toolCom.c_bg_color,
    configNme: 'couponReduceColor',
  },
];
const typeTxtStyle = [
  {
    components: toolCom.c_bg_color,
    configNme: 'couponTypeColor',
  },
];
const bntBgStyle = [
  {
    components: toolCom.c_bg_color,
    configNme: 'bntBgColor',
  },
];
const bntTxtStyle = [
  {
    components: toolCom.c_bg_color,
    configNme: 'bntTxtColor',
  },
];
const couponBgStyle = [
  {
    components: toolCom.c_bg_color,
    configNme: 'couponBgColor',
  },
];
const currencyTitleStyle = [
  {
    components: toolCom.c_slider,
    configNme: 'spacingConfig',
  },
];
const moduleColorStyle = [
  {
    components: toolCom.c_bg_color,
    configNme: 'moduleColor',
  },
];
const moduleColorStyle2 = [
  {
    components: toolCom.c_bg_color,
    configNme: 'moduleColor2',
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
    configObj.value = mobildConfigStore.defaultArray[nVal];
    configObj.value = patchConfig(configObj.value);
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
      let tempArr = [
        {
          components: toolCom.c_title,
          configNme: 'titleData',
        },
        {
          components: toolCom.c_slider,
          configNme: 'numberConfig',
        },
      ];
      rCom.value = arr.concat(tempArr);
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
    var arr = [
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
      getRComStyle(arr, nVal, type2);
    }
  },
  { deep: true },
);
watch(
  () => configObj.value?.toneConfig?.tabVal,
  (nVal, oVal) => {
    type2 = nVal;
    var arr = [
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
  if (!data.c_common_style) {
    data['c_common_style'] = {
      color: 'rgba(255,255,255,1)',
      color2: 'rgba(255,255,255,1)',
      lr: 0,
      type: 0,
    };
  }
  return data;
}
function getRComStyle(arr, type, type2) {
  if (type == 0) {
    if (type2 == 0) {
      rCom.value = [...arr, ...oneStyle, ...currencyStyle];
    } else {
      rCom.value = [...arr, ...oneStyle, ...twoStyle, ...bntBgStyle, ...couponBgStyle, ...currencyStyle];
    }
  } else if (type == 1) {
    if (type2 == 0) {
      rCom.value = [...arr, ...oneStyle, ...currencyStyle];
    } else {
      rCom.value = [
        ...arr,
        ...oneStyle,
        ...twoStyle,
        ...reduceTxtStyle,
        ...typeTxtStyle,
        ...bntBgStyle,
        ...bntTxtStyle,
        ...currencyStyle,
      ];
    }
  } else if (type == 2) {
    if (type2 == 0) {
      rCom.value = [...arr, ...oneStyle, ...currencyStyle];
    } else {
      rCom.value = [
        ...arr,
        ...oneStyle,
        ...twoStyle,
        ...reduceTxtStyle,
        ...bntBgStyle,
        ...bntTxtStyle,
        ...currencyStyle,
      ];
    }
  } else if (type == 3) {
    if (type2 == 0) {
      rCom.value = [...arr, ...oneStyle, ...currencyStyle];
    } else {
      rCom.value = [
        ...arr,
        ...oneStyle,
        ...twoStyle,
        ...reduceTxtStyle,
        ...typeTxtStyle,
        ...couponBgStyle,
        ...bntBgStyle,
        ...bntTxtStyle,
        ...currencyStyle,
      ];
    }
  } else {
    if (type2 == 0) {
      rCom.value = [...arr, ...oneStyle, ...currencyStyle];
    } else {
      rCom.value = [...arr, ...oneStyle, ...twoStyle, ...bntBgStyle, ...currencyStyle];
    }
  }
}
// 获取组件参数
function getConfig(data) {}
function handleSubmit(name) {
  let obj = {};
  obj.activeIndex = props.activeIndex;
  obj.data = configObj.value;
  mobildConfigStore.UPDATEARR(obj);
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
