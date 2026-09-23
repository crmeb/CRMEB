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

defineOptions({ name: 'c_presale', componentsName: 'home_presale' });
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
    configNme: 'tipTxtConfig',
  },
  {
    components: toolCom.c_input_item,
    configNme: 'rightBntConfig',
  },
  {
    components: toolCom.c_title,
    configNme: 'titleGoodsList',
  },
  {
    components: toolCom.c_radio,
    configNme: 'goodStyleConfig',
  },
  {
    components: toolCom.c_title,
    configNme: 'titleGoods',
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
    components: toolCom.c_radio,
    configNme: 'presaleConfig',
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
const fourColorStyle = [
  {
    components: toolCom.c_bg_color,
    configNme: 'tipsColor',
  },
];
const fourColorStyle2 = [
  {
    components: toolCom.c_bg_color,
    configNme: 'tipsColor2',
  },
];
const fourGoodsStyle = [
  {
    components: toolCom.c_bg_color,
    configNme: 'dividerColor',
  },
  {
    components: toolCom.c_title,
    configNme: 'titleGoodsStyle',
  },
  {
    components: toolCom.c_fillet,
    configNme: 'filletImg',
  },
  {
    components: toolCom.c_radio,
    configNme: 'goodsName',
  },
  {
    components: toolCom.c_bg_color,
    configNme: 'goodsNameColor',
  },
];
const goodsPriceStyle = [
  {
    components: toolCom.c_bg_color,
    configNme: 'goodsPriceColor',
  },
];
const toneStyle = [
  {
    components: toolCom.c_radio,
    configNme: 'toneConfig',
  },
];
const fiveStyle = [
  {
    components: toolCom.c_bg_color,
    configNme: 'presalePriceColor',
  },
];
const bntStyle = [
  {
    components: toolCom.c_bg_color,
    configNme: 'goodsBntColor',
  },
  {
    components: toolCom.c_bg_color,
    configNme: 'goodsBntTxtColor',
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
let type3 = 0;
let type4 = 0;
let type5 = 0;
watch(
  () => props.num,
  (nVal) => {
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
      getRComContent(arr, type, type2, type3);
    } else {
      getRComStyle(arr, type, type2, type3, type5, type4);
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
      getRComStyle(arr, nVal, type2, type3, type5, type4);
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
      getRComStyle(arr, type, nVal, type3, type5, type4);
    }
  },
  { deep: true },
);
watch(
  () => configObj.value?.goodStyleConfig?.tabVal,
  (nVal, oVal) => {
    type3 = nVal;
    var arr = [rCom.value[0]];
    if (setUp == 0) {
      getRComContent(arr, type, type2, nVal);
    } else {
      getRComStyle(arr, type, type2, nVal, type5, type4);
    }
  },
  { deep: true },
);
watch(
  () => configObj.value?.presaleConfig?.tabVal,
  (nVal, oVal) => {
    type5 = nVal;
    var arr = [rCom.value[0]];
    if (setUp) {
      getRComStyle(arr, type, type2, type3, nVal, type4);
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
      getRComStyle(arr, type, type2, type3, type5, nVal);
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
      isAll: false,
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
      isAll: false,
      title: '外边距',
      val: 0,
      min: 0,
      max: 100,
      valList: [{ val: 0 }, { val: 0 }, { val: config.mbConfig ? config.mbConfig.val : 0 }, { val: 0 }],
    };
  }
  if (!config.c_common_style && config.moduleColor) {
    config.c_common_style = {
      color: config.moduleColor.color,
      color2: config.bottomBgColor.color,
      lr: config.fillet.type ? config.fillet.val : 0,
      type: config.fillet.type,
    };
  }
  return config;
}
function getRComContent(arr, type, type2, type3) {
  if (type == 0) {
    if (type2 == 0) {
      if (type3 == 1) {
        rCom.value = [...arr, ...oneContent, ...twoContent, ...twoContentColorImg, ...threeContent, ...fourContent];
      } else {
        rCom.value = [...arr, ...oneContent, ...twoContent, ...twoContentColorImg, ...threeContent];
      }
    } else {
      if (type3 == 1) {
        rCom.value = [...arr, ...oneContent, ...twoContent, ...twoContentText, ...threeContent, ...fourContent];
      } else {
        rCom.value = [...arr, ...oneContent, ...twoContent, ...twoContentText, ...threeContent];
      }
    }
  } else {
    if (type2 == 0) {
      if (type3 == 1) {
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
      if (type3 == 1) {
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
function getRComStyle(arr, type, type2, type3, type5, type4) {
  let obj = [
    ...arr,
    ...oneStyle,
    ...twoStyle,
    ...fourStyle2,
    ...fourBntStyle,
    ...fourColorStyle2,
    ...fourGoodsStyle,
    ...toneStyle,
    ...currencyStyle,
  ];
  let obj2 = [
    ...arr,
    ...oneStyle,
    ...twoStyle,
    ...threeStyle,
    ...fourStyle2,
    ...fourBntStyle,
    ...fourColorStyle2,
    ...fourGoodsStyle,
    ...toneStyle,
    ...currencyStyle,
  ];
  let obj3 = [
    ...arr,
    ...oneStyle,
    ...fourStyle,
    ...fourBntStyle,
    ...fourColorStyle,
    ...fourGoodsStyle,
    ...toneStyle,
    ...currencyStyle,
  ];
  let obj4 = [
    ...arr,
    ...oneStyle,
    ...threeStyle,
    ...fourStyle,
    ...fourBntStyle,
    ...fourColorStyle,
    ...fourGoodsStyle,
    ...toneStyle,
    ...currencyStyle,
  ];
  if (type == 0) {
    if (type2 == 0) {
      if (type3 == 0) {
        if (type5 == 0) {
          if (type4 == 0) {
            rCom.value = obj;
          } else {
            rCom.value = [
              ...arr,
              ...oneStyle,
              ...twoStyle,
              ...fourStyle2,
              ...fourBntStyle,
              ...fourColorStyle2,
              ...fourGoodsStyle,
              ...toneStyle,
              ...fiveStyle,
              ...bntStyle,
              ...currencyStyle,
            ];
          }
        } else {
          if (type4 == 0) {
            rCom.value = obj;
          } else {
            rCom.value = [
              ...arr,
              ...oneStyle,
              ...twoStyle,
              ...fourStyle2,
              ...fourBntStyle,
              ...fourColorStyle2,
              ...fourGoodsStyle,
              ...toneStyle,
              ...fiveStyle,
              ...currencyStyle,
            ];
          }
        }
      } else if (type3 == 1) {
        if (type5 == 0) {
          if (type4 == 0) {
            rCom.value = [
              ...arr,
              ...oneStyle,
              ...twoStyle,
              ...fourStyle2,
              ...fourBntStyle,
              ...fourColorStyle2,
              ...fourGoodsStyle,
              ...goodsPriceStyle,
              ...toneStyle,
              ...currencyStyle,
            ];
          } else {
            rCom.value = [
              ...arr,
              ...oneStyle,
              ...twoStyle,
              ...fourStyle2,
              ...fourBntStyle,
              ...fourColorStyle2,
              ...fourGoodsStyle,
              ...goodsPriceStyle,
              ...toneStyle,
              ...fiveStyle,
              ...bntStyle,
              ...currencyStyle,
            ];
          }
        } else {
          if (type4 == 0) {
            rCom.value = [
              ...arr,
              ...oneStyle,
              ...twoStyle,
              ...fourStyle2,
              ...fourBntStyle,
              ...fourColorStyle2,
              ...fourGoodsStyle,
              ...goodsPriceStyle,
              ...toneStyle,
              ...currencyStyle,
            ];
          } else {
            rCom.value = [
              ...arr,
              ...oneStyle,
              ...twoStyle,
              ...fourStyle2,
              ...fourBntStyle,
              ...fourColorStyle2,
              ...fourGoodsStyle,
              ...goodsPriceStyle,
              ...toneStyle,
              ...fiveStyle,
              ...currencyStyle,
            ];
          }
        }
      } else if (type3 == 2) {
        if (type4 == 0) {
          rCom.value = obj;
        } else {
          rCom.value = [
            ...arr,
            ...oneStyle,
            ...twoStyle,
            ...fourStyle2,
            ...fourBntStyle,
            ...fourColorStyle2,
            ...fourGoodsStyle,
            ...toneStyle,
            ...fiveStyle,
            ...currencyStyle,
          ];
        }
      } else {
        if (type4 == 0) {
          rCom.value = [
            ...arr,
            ...oneStyle,
            ...twoStyle,
            ...fourStyle2,
            ...fourBntStyle,
            ...fourColorStyle2,
            ...fourGoodsStyle,
            ...toneStyle,
            ...currencyStyle,
          ];
        } else {
          rCom.value = [
            ...arr,
            ...oneStyle,
            ...twoStyle,
            ...fourStyle2,
            ...fourBntStyle,
            ...fourColorStyle2,
            ...fourGoodsStyle,
            ...toneStyle,
            ...fiveStyle,
            ...currencyStyle,
          ];
        }
      }
    } else {
      if (type3 == 0) {
        if (type5 == 0) {
          if (type4 == 0) {
            rCom.value = obj2;
          } else {
            rCom.value = [
              ...arr,
              ...oneStyle,
              ...twoStyle,
              ...threeStyle,
              ...fourStyle2,
              ...fourBntStyle,
              ...fourColorStyle2,
              ...fourGoodsStyle,
              ...toneStyle,
              ...fiveStyle,
              ...bntStyle,
              ...currencyStyle,
            ];
          }
        } else {
          if (type4 == 0) {
            rCom.value = obj2;
          } else {
            rCom.value = [
              ...arr,
              ...oneStyle,
              ...twoStyle,
              ...threeStyle,
              ...fourStyle2,
              ...fourBntStyle,
              ...fourColorStyle2,
              ...fourGoodsStyle,
              ...toneStyle,
              ...fiveStyle,
              ...currencyStyle,
            ];
          }
        }
      } else if (type3 == 1) {
        if (type5 == 0) {
          if (type4 == 0) {
            rCom.value = [
              ...arr,
              ...oneStyle,
              ...twoStyle,
              ...threeStyle,
              ...fourStyle2,
              ...fourBntStyle,
              ...fourColorStyle2,
              ...fourGoodsStyle,
              ...goodsPriceStyle,
              ...toneStyle,
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
              ...fourColorStyle2,
              ...fourGoodsStyle,
              ...goodsPriceStyle,
              ...toneStyle,
              ...fiveStyle,
              ...bntStyle,
              ...currencyStyle,
            ];
          }
        } else {
          if (type4 == 0) {
            rCom.value = [
              ...arr,
              ...oneStyle,
              ...twoStyle,
              ...threeStyle,
              ...fourStyle2,
              ...fourBntStyle,
              ...fourColorStyle2,
              ...fourGoodsStyle,
              ...goodsPriceStyle,
              ...toneStyle,
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
              ...fourColorStyle2,
              ...fourGoodsStyle,
              ...goodsPriceStyle,
              ...toneStyle,
              ...fiveStyle,
              ...currencyStyle,
            ];
          }
        }
      } else if (type3 == 2) {
        if (type4 == 0) {
          rCom.value = obj2;
        } else {
          rCom.value = [
            ...arr,
            ...oneStyle,
            ...twoStyle,
            ...threeStyle,
            ...fourStyle2,
            ...fourBntStyle,
            ...fourColorStyle2,
            ...fourGoodsStyle,
            ...toneStyle,
            ...fiveStyle,
            ...currencyStyle,
          ];
        }
      } else {
        if (type4 == 0) {
          rCom.value = [
            ...arr,
            ...oneStyle,
            ...twoStyle,
            ...threeStyle,
            ...fourStyle2,
            ...fourBntStyle,
            ...fourColorStyle2,
            ...fourGoodsStyle,
            ...toneStyle,
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
            ...fourColorStyle2,
            ...fourGoodsStyle,
            ...toneStyle,
            ...fiveStyle,
            ...currencyStyle,
          ];
        }
      }
    }
  } else {
    if (type2 == 0) {
      if (type3 == 0) {
        if (type5 == 0) {
          if (type4 == 0) {
            rCom.value = obj3;
          } else {
            rCom.value = [
              ...arr,
              ...oneStyle,
              ...fourStyle,
              ...fourBntStyle,
              ...fourColorStyle,
              ...fourGoodsStyle,
              ...toneStyle,
              ...fiveStyle,
              ...bntStyle,
              ...currencyStyle,
            ];
          }
        } else {
          if (type4 == 0) {
            rCom.value = obj3;
          } else {
            rCom.value = [
              ...arr,
              ...oneStyle,
              ...fourStyle,
              ...fourBntStyle,
              ...fourColorStyle,
              ...fourGoodsStyle,
              ...toneStyle,
              ...fiveStyle,
              ...currencyStyle,
            ];
          }
        }
      } else if (type3 == 1) {
        if (type5 == 0) {
          if (type4 == 0) {
            rCom.value = [
              ...arr,
              ...oneStyle,
              ...fourStyle,
              ...fourBntStyle,
              ...fourColorStyle,
              ...fourGoodsStyle,
              ...goodsPriceStyle,
              ...toneStyle,
              ...currencyStyle,
            ];
          } else {
            rCom.value = [
              ...arr,
              ...oneStyle,
              ...fourStyle,
              ...fourBntStyle,
              ...fourColorStyle,
              ...fourGoodsStyle,
              ...goodsPriceStyle,
              ...toneStyle,
              ...fiveStyle,
              ...bntStyle,
              ...currencyStyle,
            ];
          }
        } else {
          if (type4 == 0) {
            rCom.value = [
              ...arr,
              ...oneStyle,
              ...fourStyle,
              ...fourBntStyle,
              ...fourColorStyle,
              ...fourGoodsStyle,
              ...goodsPriceStyle,
              ...toneStyle,
              ...currencyStyle,
            ];
          } else {
            rCom.value = [
              ...arr,
              ...oneStyle,
              ...fourStyle,
              ...fourBntStyle,
              ...fourColorStyle,
              ...fourGoodsStyle,
              ...goodsPriceStyle,
              ...toneStyle,
              ...fiveStyle,
              ...currencyStyle,
            ];
          }
        }
      } else if (type3 == 2) {
        if (type4 == 0) {
          rCom.value = obj3;
        } else {
          rCom.value = [
            ...arr,
            ...oneStyle,
            ...fourStyle,
            ...fourBntStyle,
            ...fourColorStyle,
            ...fourGoodsStyle,
            ...toneStyle,
            ...fiveStyle,
            ...currencyStyle,
          ];
        }
      } else {
        if (type4 == 0) {
          rCom.value = obj3;
        } else {
          rCom.value = [
            ...arr,
            ...oneStyle,
            ...fourStyle,
            ...fourBntStyle,
            ...fourColorStyle,
            ...fourGoodsStyle,
            ...toneStyle,
            ...fiveStyle,
            ...currencyStyle,
          ];
        }
      }
    } else {
      if (type3 == 0) {
        if (type5 == 0) {
          if (type4 == 0) {
            rCom.value = obj4;
          } else {
            rCom.value = [
              ...arr,
              ...oneStyle,
              ...threeStyle,
              ...fourStyle,
              ...fourBntStyle,
              ...fourColorStyle,
              ...fourGoodsStyle,
              ...toneStyle,
              ...fiveStyle,
              ...bntStyle,
              ...currencyStyle,
            ];
          }
        } else {
          if (type4 == 0) {
            rCom.value = obj4;
          } else {
            rCom.value = [
              ...arr,
              ...oneStyle,
              ...threeStyle,
              ...fourStyle,
              ...fourBntStyle,
              ...fourColorStyle,
              ...fourGoodsStyle,
              ...toneStyle,
              ...fiveStyle,
              ...currencyStyle,
            ];
          }
        }
      } else if (type3 == 1) {
        if (type5 == 0) {
          if (type4 == 0) {
            rCom.value = [
              ...arr,
              ...oneStyle,
              ...threeStyle,
              ...fourStyle,
              ...fourBntStyle,
              ...fourColorStyle,
              ...fourGoodsStyle,
              ...goodsPriceStyle,
              ...toneStyle,
              ...currencyStyle,
            ];
          } else {
            rCom.value = [
              ...arr,
              ...oneStyle,
              ...threeStyle,
              ...fourStyle,
              ...fourBntStyle,
              ...fourColorStyle,
              ...fourGoodsStyle,
              ...goodsPriceStyle,
              ...toneStyle,
              ...fiveStyle,
              ...bntStyle,
              ...currencyStyle,
            ];
          }
        } else {
          if (type4 == 0) {
            rCom.value = [
              ...arr,
              ...oneStyle,
              ...threeStyle,
              ...fourStyle,
              ...fourBntStyle,
              ...fourColorStyle,
              ...fourGoodsStyle,
              ...goodsPriceStyle,
              ...toneStyle,
              ...currencyStyle,
            ];
          } else {
            rCom.value = [
              ...arr,
              ...oneStyle,
              ...threeStyle,
              ...fourStyle,
              ...fourBntStyle,
              ...fourColorStyle,
              ...fourGoodsStyle,
              ...goodsPriceStyle,
              ...toneStyle,
              ...fiveStyle,
              ...currencyStyle,
            ];
          }
        }
      } else if (type3 == 2) {
        if (type4 == 0) {
          rCom.value = obj4;
        } else {
          rCom.value = [
            ...arr,
            ...oneStyle,
            ...threeStyle,
            ...fourStyle,
            ...fourBntStyle,
            ...fourColorStyle,
            ...fourGoodsStyle,
            ...toneStyle,
            ...fiveStyle,
            ...currencyStyle,
          ];
        }
      } else {
        if (type4 == 0) {
          rCom.value = obj4;
        } else {
          rCom.value = [
            ...arr,
            ...oneStyle,
            ...threeStyle,
            ...fourStyle,
            ...fourBntStyle,
            ...fourColorStyle,
            ...fourGoodsStyle,
            ...toneStyle,
            ...fiveStyle,
            ...currencyStyle,
          ];
        }
      }
    }
  }
}
// 获取组件参数
function getConfig(data) {}
</script>

<style scoped></style>
