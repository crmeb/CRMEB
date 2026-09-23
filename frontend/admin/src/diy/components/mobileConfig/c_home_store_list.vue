<template>
  <div class="mobile-config">
    <div v-for="(item, key) in rCom" :key="key">
      <component
        :is="item.components"
        :configObj="configObj"
        ref="childData"
        @getConfig="getConfig"
        :key="key"
        :configNme="item.configNme"
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

defineOptions({ name: 'c_home_store_list', componentsName: 'home_store_list' });
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
];
const oneContentImg = [
  {
    components: toolCom.c_upload_img,
    configNme: 'imgBgConfig',
  },
];
const twoContent = [
  {
    components: toolCom.c_radio,
    configNme: 'titleConfig',
  },
];
const twoContentImg = [
  {
    components: toolCom.c_upload_img,
    configNme: 'imgConfig',
  },
];
const twoContentColorImg = [
  {
    components: toolCom.c_upload_img,
    configNme: 'imgColorConfig',
  },
];
const twoContentText = [
  {
    components: toolCom.c_input_item,
    configNme: 'titleTxtConfig',
  },
];
const threeContent = [
  {
    components: toolCom.c_input_item,
    configNme: 'rightBntConfig',
  },
  {
    components: toolCom.c_title,
    configNme: 'titleStoreList',
  },
  {
    components: toolCom.c_radio,
    configNme: 'storeStyleConfig',
  },
  {
    components: toolCom.c_title,
    configNme: 'titleStore',
  },
  {
    components: toolCom.c_slider,
    configNme: 'numberConfig',
  },
  {
    components: toolCom.c_checkbox,
    configNme: 'checkboxInfo',
  },
];
const fourContent = [
  {
    components: toolCom.c_title,
    configNme: 'titleGoods',
  },
  {
    components: toolCom.c_slider,
    configNme: 'numberGoodsConfig',
  },
  {
    components: toolCom.c_checkbox,
    configNme: 'checkboxGoodsInfo',
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
    components: toolCom.c_bg_color,
    configNme: 'headerBgColor',
  },
];
const threeStyle = [
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
const fourStyle = [
  {
    components: toolCom.c_bg_color,
    configNme: 'headerBntColor',
  },
];
const fourStyle2 = [
  {
    components: toolCom.c_bg_color,
    configNme: 'headerBntColor2',
  },
];
const fourBntStyle = [
  {
    components: toolCom.c_slider,
    configNme: 'bntNumber',
  },
];
const fourStoreStyle = [
  {
    components: toolCom.c_title,
    configNme: 'titleStoreStyle',
  },
  {
    components: toolCom.c_fillet,
    configNme: 'filletStoreImg',
  },
  {
    components: toolCom.c_slider,
    configNme: 'storeNumber',
  },
  {
    components: toolCom.c_radio,
    configNme: 'storeName',
  },
  {
    components: toolCom.c_bg_color,
    configNme: 'storeNameColor',
  },
  {
    components: toolCom.c_bg_color,
    configNme: 'deliveryBg',
  },
  {
    components: toolCom.c_bg_color,
    configNme: 'deliveryText',
  },
  {
    components: toolCom.c_bg_color,
    configNme: 'storeBg',
  },
  {
    components: toolCom.c_bg_color,
    configNme: 'storeText',
  },
];
const goodsStyle = [
  {
    components: toolCom.c_title,
    configNme: 'titleGoodsStyle',
  },
  {
    components: toolCom.c_radio,
    configNme: 'goodsName',
  },
  {
    components: toolCom.c_bg_color,
    configNme: 'goodsNameColor',
  },
  {
    components: toolCom.c_radio,
    configNme: 'toneConfig',
  },
];
const goodsPriceStyle = [
  {
    components: toolCom.c_bg_color,
    configNme: 'goodsPriceColor',
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
let type3 = 0;
let type4 = 0;
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
    var arr = [rCom.value[0]];
    if (nVal == 0) {
      getRComContent(arr, type, type2, type3);
    } else {
      getRComStyle(arr, type, type2, type3, type4);
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
      getRComContent(arr, nVal, type2, type3);
    } else {
      getRComStyle(arr, nVal, type2, type3, type4);
    }
  },
  { deep: true },
);
watch(
  () => configObj.value?.titleConfig?.tabVal,
  (nVal, oVal) => {
    type2 = nVal;
    var arr = [rCom.value[0]];
    if (setUp == 0) {
      getRComContent(arr, type, nVal, type3);
    } else {
      getRComStyle(arr, type, nVal, type3, type4);
    }
  },
  { deep: true },
);
watch(
  () => configObj.value?.storeStyleConfig?.tabVal,
  (nVal, oVal) => {
    type3 = nVal;
    var arr = [rCom.value[0]];
    if (setUp == 0) {
      getRComContent(arr, type, type2, nVal);
    } else {
      getRComStyle(arr, type, type2, nVal, type4);
    }
  },
  { deep: true },
);
watch(
  () => configObj.value?.toneConfig?.tabVal,
  (nVal, oVal) => {
    type4 = nVal;
    var arr = [rCom.value[0]];
    if (setUp) {
      getRComStyle(arr, type, type2, type3, nVal);
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
function getRComContent(arr, type, type2, type3) {
  if (type == 0) {
    if (type2 == 0) {
      if (type3 == 0) {
        rCom.value = [...arr, ...oneContent, ...twoContent, ...twoContentColorImg, ...threeContent, ...fourContent];
      } else {
        rCom.value = [...arr, ...oneContent, ...twoContent, ...twoContentColorImg, ...threeContent];
      }
    } else {
      if (type3 == 0) {
        rCom.value = [...arr, ...oneContent, ...twoContent, ...twoContentText, ...threeContent, ...fourContent];
      } else {
        rCom.value = [...arr, ...oneContent, ...twoContent, ...twoContentText, ...threeContent];
      }
    }
  } else {
    if (type2 == 0) {
      if (type3 == 0) {
        rCom.value = [
          ...arr,
          ...oneContent,
          ...oneContentImg,
          ...twoContent,
          ...twoContentImg,
          ...threeContent,
          ...fourContent,
        ];
      } else {
        rCom.value = [...arr, ...oneContent, ...oneContentImg, ...twoContent, ...twoContentImg, ...threeContent];
      }
    } else {
      if (type3 == 0) {
        rCom.value = [
          ...arr,
          ...oneContent,
          ...oneContentImg,
          ...twoContent,
          ...twoContentText,
          ...threeContent,
          ...fourContent,
        ];
      } else {
        rCom.value = [...arr, ...oneContent, ...oneContentImg, ...twoContent, ...twoContentText, ...threeContent];
      }
    }
  }
}
function getRComStyle(arr, type, type2, type3, type4) {
  if (type == 0) {
    if (type2 == 0) {
      if (type3 == 0) {
        if (type4 == 0) {
          rCom.value = [
            ...arr,
            ...oneStyle,
            ...twoStyle,
            ...fourStyle2,
            ...fourBntStyle,
            ...fourStoreStyle,
            ...goodsStyle,
            ...currencyStyle,
          ];
        } else {
          rCom.value = [
            ...arr,
            ...oneStyle,
            ...twoStyle,
            ...fourStyle2,
            ...fourBntStyle,
            ...fourStoreStyle,
            ...goodsStyle,
            ...goodsPriceStyle,
            ...currencyStyle,
          ];
        }
      } else {
        rCom.value = [
          ...arr,
          ...oneStyle,
          ...twoStyle,
          ...fourStyle2,
          ...fourBntStyle,
          ...fourStoreStyle,
          ...currencyStyle,
        ];
      }
    } else {
      if (type3 == 0) {
        if (type4 == 0) {
          rCom.value = [
            ...arr,
            ...oneStyle,
            ...twoStyle,
            ...threeStyle,
            ...fourStyle2,
            ...fourBntStyle,
            ...fourStoreStyle,
            ...goodsStyle,
            ...currencyStyle,
          ];
        } else {
          rCom.value = [
            ...arr,
            ...oneStyle,
            ...twoStyle,
            ...threeStyle,
            ...fourStyle2,
            ...fourBntStyle,
            ...fourStoreStyle,
            ...goodsStyle,
            ...goodsPriceStyle,
            ...currencyStyle,
          ];
        }
      } else {
        rCom.value = [
          ...arr,
          ...oneStyle,
          ...twoStyle,
          ...threeStyle,
          ...fourStyle2,
          ...fourBntStyle,
          ...fourStoreStyle,
          ...currencyStyle,
        ];
      }
    }
  } else {
    if (type2 == 0) {
      if (type3 == 0) {
        if (type4 == 0) {
          rCom.value = [
            ...arr,
            ...oneStyle,
            ...fourStyle,
            ...fourBntStyle,
            ...fourStoreStyle,
            ...goodsStyle,
            ...currencyStyle,
          ];
        } else {
          rCom.value = [
            ...arr,
            ...oneStyle,
            ...fourStyle,
            ...fourBntStyle,
            ...fourStoreStyle,
            ...goodsStyle,
            ...goodsPriceStyle,
            ...currencyStyle,
          ];
        }
      } else {
        rCom.value = [...arr, ...oneStyle, ...fourStyle, ...fourBntStyle, ...fourStoreStyle, ...currencyStyle];
      }
    } else {
      if (type3 == 0) {
        if (type4 == 0) {
          rCom.value = [
            ...arr,
            ...oneStyle,
            ...threeStyle,
            ...fourStyle,
            ...fourBntStyle,
            ...fourStoreStyle,
            ...goodsStyle,
            ...currencyStyle,
          ];
        } else {
          rCom.value = [
            ...arr,
            ...oneStyle,
            ...threeStyle,
            ...fourStyle,
            ...fourBntStyle,
            ...fourStoreStyle,
            ...goodsStyle,
            ...goodsPriceStyle,
            ...currencyStyle,
          ];
        }
      } else {
        rCom.value = [
          ...arr,
          ...oneStyle,
          ...threeStyle,
          ...fourStyle,
          ...fourBntStyle,
          ...fourStoreStyle,
          ...currencyStyle,
        ];
      }
    }
  }
}
// 获取组件参数
function getConfig(data) {}
</script>

<style scoped></style>
