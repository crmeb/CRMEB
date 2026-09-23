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

defineOptions({ name: 'c_points_mall', cname: '积分商城', componentsName: 'points_mall' });
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
    configNme: 'goodStyleConfig',
  },
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
const twoContentImg2 = [
  {
    components: toolCom.c_upload_img,
    configNme: 'imgConfig2',
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
    configNme: 'titleGoods',
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
const fiveStyle = [
  {
    components: toolCom.c_slider,
    configNme: 'bntNumber',
  },
  {
    components: toolCom.c_title,
    configNme: 'titleGoodsStyle',
  },
  {
    components: toolCom.c_fillet,
    configNme: 'filletImg',
  },
];
const goodsNameStyle = [
  {
    components: toolCom.c_bg_color,
    configNme: 'goodsNameColor',
  },
];
const goodsNameStyle2 = [
  {
    components: toolCom.c_bg_color,
    configNme: 'goodsNameColor2',
  },
];
const toneStyle = [
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
const goodsPriceStyle2 = [
  {
    components: toolCom.c_bg_color,
    configNme: 'goodsPriceColor2',
  },
  {
    components: toolCom.c_bg_color,
    configNme: 'goodsUnitPriceColor2',
  },
];
const goodsUnitPriceStyle = [
  {
    components: toolCom.c_bg_color,
    configNme: 'goodsUnitPriceColor',
  },
];
const priceBgStyle = [
  {
    components: toolCom.c_bg_color,
    configNme: 'priceBgColor',
  },
];
const titleStyle = [
  {
    components: toolCom.c_title,
    configNme: 'titleCurrency',
  },
];
const moduleStyle = [
  {
    components: toolCom.c_bg_color,
    configNme: 'moduleColor',
  },
];
const moduleStyle2 = [
  {
    components: toolCom.c_bg_color,
    configNme: 'moduleColor2',
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
      {
        components: toolCom.c_card_select,
        configNme: 'goodStyleConfig',
      },
      {
        components: toolCom.c_set_up,
        configNme: 'setUp',
      },
    ];
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
    var arr = [
      {
        components: toolCom.c_card_select,
        configNme: 'goodStyleConfig',
      },
      {
        components: toolCom.c_set_up,
        configNme: 'setUp',
      },
    ];
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
    var arr = [
      {
        components: toolCom.c_card_select,
        configNme: 'goodStyleConfig',
      },
      {
        components: toolCom.c_set_up,
        configNme: 'setUp',
      },
    ];
    if (setUp == 0) {
      getRComContent(arr, type, nVal, type3);
    } else {
      getRComStyle(arr, type, nVal, type3, type4);
    }
  },
  { deep: true },
);
watch(
  () => configObj.value?.goodStyleConfig?.tabVal,
  (nVal, oVal) => {
    type3 = nVal;
    var arr = [
      {
        components: toolCom.c_card_select,
        configNme: 'goodStyleConfig',
      },
      {
        components: toolCom.c_set_up,
        configNme: 'setUp',
      },
    ];
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
    var arr = [
      {
        components: toolCom.c_card_select,
        configNme: 'goodStyleConfig',
      },
      {
        components: toolCom.c_set_up,
        configNme: 'setUp',
      },
    ];
    if (setUp) {
      getRComStyle(arr, type, type2, type3, nVal);
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
      title: '内边距',
      val: 0,
      min: 0,
      max: 100,
      isAll: false,
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
      title: '外边距',
      isAll: false,
      val: 0,
      min: 0,
      max: 100,
      valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
    };
    if (data.mbConfig) data.marginConfig.valList[0].val = data.mbConfig.val;
  }
  if (!data.componentBgConfig) {
    data['componentBgConfig'] = {
      title: '背景设置',
      tabVal: 0,
      tabList: [{ name: '颜色' }, { name: '图片' }],
      colorConfig: {
        title: '背景颜色',
        default: [
          {
            item: '#F5F5F5',
          },
          {
            item: '#F5F5F5',
          },
        ],
        color: [
          {
            item: '#F5F5F5',
          },
          {
            item: '#F5F5F5',
          },
        ],
      },
      imgBgConfig: {
        info: '建议：750px * 100 ~ 200px',
        url: '',
        type: 'code',
        delType: 1,
        name: '背景图片',
      },
    };
  }
  if (!data.zIndexConfig) {
    data['zIndexConfig'] = {
      title: '组件上浮',
      val: 0,
      min: 0,
    };
  }
  if (!data.borderConfig) {
    data['borderConfig'] = {
      title: '组件边框',
      show: false,
      color: {
        item: '#E5E5E5',
      },
      scale: {
        val: 1,
        min: 1,
      },
    };
  }
  if (!data.shadowConfig) {
    data['shadowConfig'] = {
      title: '组件阴影',
      show: false,
      color: {
        item: '#E5E5E5',
      },
      scale: {
        val: 1,
        min: 1,
      },
    };
  }
  if (!data.c_common_style) {
    data.c_common_style = {
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
        rCom.value = [...arr, ...oneContent, ...twoContent, ...twoContentImg, ...threeContent];
      } else {
        rCom.value = [...arr, ...oneContent, ...twoContent, ...twoContentImg2, ...threeContent];
      }
    } else {
      rCom.value = [...arr, ...oneContent, ...twoContent, ...twoContentText, ...threeContent];
    }
  } else {
    if (type2 == 0) {
      rCom.value = [...arr, ...oneContent, ...oneContentImg, ...twoContent, ...twoContentImg, ...threeContent];
    } else {
      rCom.value = [...arr, ...oneContent, ...oneContentImg, ...twoContent, ...twoContentText, ...threeContent];
    }
  }
}
function getRComStyle(arr, type, type2, type3, type4) {
  let obj = [
    ...arr,
    ...oneStyle,
    ...twoStyle,
    ...fourStyle2,
    ...fiveStyle,
    ...toneStyle,
    ...moduleStyle2,
    ...currencyStyle,
  ];
  let obj2 = [
    ...arr,
    ...oneStyle,
    ...twoStyle,
    ...threeStyle,
    ...fourStyle2,
    ...fiveStyle,
    ...toneStyle,
    ...moduleStyle2,
    ...currencyStyle,
  ];
  let obj3 = [...arr, ...oneStyle, ...fourStyle, ...fiveStyle, ...toneStyle, ...currencyStyle];
  let obj4 = [...arr, ...oneStyle, ...threeStyle, ...fourStyle, ...fiveStyle, ...toneStyle, ...currencyStyle];
  if (type == 0) {
    if (type2 == 0) {
      if (type3 == 0) {
        if (type4 == 0) {
          rCom.value = obj;
        } else {
          rCom.value = [
            ...arr,
            ...oneStyle,
            ...twoStyle,
            ...fourStyle2,
            ...fiveStyle,
            ...toneStyle,
            ...goodsPriceStyle,
            ...priceBgStyle,
            ...moduleStyle2,
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
            ...fiveStyle,
            ...goodsNameStyle,
            ...toneStyle,
            ...moduleStyle2,
            ...currencyStyle,
          ];
        } else {
          rCom.value = [
            ...arr,
            ...oneStyle,
            ...twoStyle,
            ...fourStyle2,
            ...fiveStyle,
            ...goodsNameStyle,
            ...toneStyle,
            ...goodsPriceStyle2,
            ...moduleStyle2,
            ...currencyStyle,
          ];
        }
      }
    } else {
      if (type3 == 0) {
        if (type4 == 0) {
          rCom.value = obj2;
        } else {
          rCom.value = [
            ...arr,
            ...oneStyle,
            ...twoStyle,
            ...threeStyle,
            ...fourStyle2,
            ...fiveStyle,
            ...toneStyle,
            ...goodsPriceStyle,
            ...priceBgStyle,
            ...moduleStyle2,
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
            ...fiveStyle,
            ...goodsNameStyle,
            ...toneStyle,
            ...moduleStyle2,
            ...currencyStyle,
          ];
        } else {
          rCom.value = [
            ...arr,
            ...oneStyle,
            ...twoStyle,
            ...threeStyle,
            ...fourStyle2,
            ...fiveStyle,
            ...goodsNameStyle,
            ...toneStyle,
            ...goodsPriceStyle2,
            ...moduleStyle2,
            ...currencyStyle,
          ];
        }
      }
    }
  } else {
    if (type2 == 0) {
      if (type3 == 0) {
        if (type4 == 0) {
          rCom.value = obj3;
        } else {
          rCom.value = [
            ...arr,
            ...oneStyle,
            ...fourStyle,
            ...fiveStyle,
            ...toneStyle,
            ...goodsPriceStyle,
            ...priceBgStyle,
            ...currencyStyle,
          ];
        }
      } else {
        if (type4 == 0) {
          rCom.value = [
            ...arr,
            ...oneStyle,
            ...fourStyle,
            ...fiveStyle,
            ...goodsNameStyle2,
            ...toneStyle,
            ...currencyStyle,
          ];
        } else {
          rCom.value = [
            ...arr,
            ...oneStyle,
            ...fourStyle,
            ...fiveStyle,
            ...goodsNameStyle2,
            ...toneStyle,
            ...goodsPriceStyle,
            ...goodsUnitPriceStyle,
            ...currencyStyle,
          ];
        }
      }
    } else {
      if (type3 == 0) {
        if (type4 == 0) {
          rCom.value = obj4;
        } else {
          rCom.value = [
            ...arr,
            ...oneStyle,
            ...threeStyle,
            ...fourStyle,
            ...fiveStyle,
            ...toneStyle,
            ...goodsPriceStyle,
            ...priceBgStyle,
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
            ...fiveStyle,
            ...goodsNameStyle2,
            ...toneStyle,
            ...currencyStyle,
          ];
        } else {
          rCom.value = [
            ...arr,
            ...oneStyle,
            ...threeStyle,
            ...fourStyle,
            ...fiveStyle,
            ...goodsNameStyle2,
            ...toneStyle,
            ...goodsPriceStyle,
            ...goodsUnitPriceStyle,
            ...currencyStyle,
          ];
        }
      }
    }
  }
}
function getConfig(data) {}
</script>

<style scoped></style>
