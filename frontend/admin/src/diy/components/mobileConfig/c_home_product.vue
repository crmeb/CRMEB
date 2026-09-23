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
import { getProProduct } from '@diy/api';
import { useMobildConfigStore } from '@diy/store/mobildConfig';

defineOptions({ name: 'c_home_product', cname: '商品选项卡', componentsName: 'home_product' });
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
  //   components: toolCom.c_title,
  //   configNme: 'titleLeft',
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
    configNme: 'slideConfig',
  },
  {
    components: toolCom.c_title,
    configNme: 'titleTab',
  },
  {
    components: toolCom.c_promotion,
    configNme: 'tabConfig',
  },
  {
    components: toolCom.c_title,
    configNme: 'titleCart',
  },
  {
    components: toolCom.c_radio,
    configNme: 'cartConfig',
  },
];
const twoContent = [
  {
    components: toolCom.c_button_img,
    configNme: 'bntStyleConfig',
  },
  {
    components: toolCom.c_radio,
    configNme: 'bntConfig',
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
    configNme: 'textColor2',
  },
];
const threeStyle2 = [
  {
    components: toolCom.c_bg_color,
    configNme: 'textColor',
  },
];
const threeStyle3 = [
  {
    components: toolCom.c_bg_color,
    configNme: 'textColor3',
  },
];
const fourStyle = [
  {
    components: toolCom.c_title,
    configNme: 'titleCart',
  },
  {
    components: toolCom.c_radio,
    configNme: 'toneCartConfig',
  },
];
const fourStyle2 = [
  {
    components: toolCom.c_bg_color,
    configNme: 'goodsPriceColor',
  },
  {
    components: toolCom.c_bg_color,
    configNme: 'bntBgColor',
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
    var arr = [
      // {
      //   components: toolCom.c_title,
      //   configNme: 'titleLeft',
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
      getRComContent(arr, type3);
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
        configNme: 'styleConfig',
      },
      {
        components: toolCom.c_set_up,
        configNme: 'setUp',
      },
    ];
    if (setUp) {
      getRComStyle(arr, nVal, type2, type3, type4);
    }
  },
);
watch(
  () => configObj.value?.cartConfig?.tabVal,
  (nVal, oVal) => {
    type3 = nVal;
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
    if (setUp == 0) {
      getRComContent(arr, nVal);
    } else {
      getRComStyle(arr, type, type2, nVal, type4);
    }
  },
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
      getRComStyle(arr, type, nVal, type3, type4);
    }
  },
);
watch(
  () => configObj.value?.toneCartConfig?.tabVal,
  (nVal, oVal) => {
    type4 = nVal;
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
      getRComStyle(arr, type, type2, type3, nVal);
    }
  },
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
    data.paddingConfig = {
      title: '内边距',
      val: 0,
      min: 0,
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
    data.marginConfig = {
      title: '外边距',
      val: 0,
      min: 0,
      isAll: false,
      valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
    };
    if (data.mbConfig) data.marginConfig.valList[0].val = data.mbConfig.val;
  }
  return data;
}
function getRComContent(arr, type3) {
  if (type3 == 0) {
    rCom.value = [...arr, ...oneContent, ...twoContent];
  } else {
    rCom.value = [...arr, ...oneContent];
  }
}
function getRComStyle(arr, type, type2, type3, type4) {
  let obj = [...arr, ...oneStyle, ...currencyStyle];
  let obj2 = [...arr, ...oneStyle, ...fourStyle, ...currencyStyle];
  if (type == 0) {
    if (type2 == 0) {
      if (type3 == 0) {
        if (type4 == 0) {
          rCom.value = obj2;
        } else {
          rCom.value = [...arr, ...oneStyle, ...fourStyle, ...fourStyle2, ...currencyStyle];
        }
      } else {
        rCom.value = obj;
      }
    } else {
      if (type3 == 0) {
        if (type4 == 0) {
          rCom.value = [...arr, ...oneStyle, ...twoStyle, ...threeStyle, ...fourStyle, ...currencyStyle];
        } else {
          rCom.value = [...arr, ...oneStyle, ...twoStyle, ...threeStyle, ...fourStyle, ...fourStyle2, ...currencyStyle];
        }
      } else {
        rCom.value = [...arr, ...oneStyle, ...twoStyle, ...threeStyle, ...fourStyle, ...currencyStyle];
      }
    }
  } else if (type == 1) {
    if (type2 == 0) {
      if (type3 == 0) {
        if (type4 == 0) {
          rCom.value = obj2;
        } else {
          rCom.value = [...arr, ...oneStyle, ...fourStyle, ...fourStyle2, ...currencyStyle];
        }
      } else {
        rCom.value = obj;
      }
    } else {
      if (type3 == 0) {
        if (type4 == 0) {
          rCom.value = [...arr, ...oneStyle, ...twoStyle, ...threeStyle2, ...fourStyle, ...currencyStyle];
        } else {
          rCom.value = [
            ...arr,
            ...oneStyle,
            ...twoStyle,
            ...threeStyle2,
            ...fourStyle,
            ...fourStyle2,
            ...currencyStyle,
          ];
        }
      } else {
        rCom.value = [...arr, ...oneStyle, ...twoStyle, ...threeStyle2, ...fourStyle, ...currencyStyle];
      }
    }
  } else if (type == 2) {
    if (type2 == 0) {
      if (type3 == 0) {
        if (type4 == 0) {
          rCom.value = obj2;
        } else {
          rCom.value = [...arr, ...oneStyle, ...fourStyle, ...fourStyle2, ...currencyStyle];
        }
      } else {
        rCom.value = obj;
      }
    } else {
      if (type3 == 0) {
        if (type4 == 0) {
          rCom.value = [...arr, ...oneStyle, ...twoStyle2, ...threeStyle, ...fourStyle, ...currencyStyle];
        } else {
          rCom.value = [
            ...arr,
            ...oneStyle,
            ...twoStyle2,
            ...threeStyle,
            ...fourStyle,
            ...fourStyle2,
            ...currencyStyle,
          ];
        }
      } else {
        rCom.value = [...arr, ...oneStyle, ...twoStyle2, ...threeStyle, ...fourStyle, ...currencyStyle];
      }
    }
  } else {
    if (type2 == 0) {
      if (type3 == 0) {
        if (type4 == 0) {
          rCom.value = obj2;
        } else {
          rCom.value = [...arr, ...oneStyle, ...fourStyle, ...fourStyle2, ...currencyStyle];
        }
      } else {
        rCom.value = obj;
      }
    } else {
      if (type3 == 0) {
        if (type4 == 0) {
          rCom.value = [...arr, ...oneStyle, ...twoStyle, ...threeStyle3, ...fourStyle, ...currencyStyle];
        } else {
          rCom.value = [
            ...arr,
            ...oneStyle,
            ...twoStyle,
            ...threeStyle3,
            ...fourStyle,
            ...fourStyle2,
            ...currencyStyle,
          ];
        }
      } else {
        rCom.value = [...arr, ...oneStyle, ...twoStyle, ...threeStyle3, ...fourStyle, ...currencyStyle];
      }
    }
  }
}
function getConfig(data) {
  // 局部改名避免遮蔽同名 ref（原 let configObj = configObj.value... 会触发 TDZ 报错）
  let tabItem = configObj.value.tabConfig.list[configObj.value.tabConfig.tabCur];
  let activeValue = tabItem.selectConfig.activeValue;
  if (!data.name) {
    return;
  }
  if (data.name == 'selectType' && data.values == 1) {
    return;
  }
  let type = tabItem.tabVal;
  let dataObj = {
    page: 1,
    limit: tabItem.numConfig.val,
    priceOrder: tabItem.goodsSort == 2 ? 'desc' : '',
    salesOrder: tabItem.goodsSort == 1 ? 'desc' : '',
  };
  if (type == 1) {
    configObj.value.productList.list = [];
    return;
  } else if (type == 3) {
    dataObj.cate_id = activeValue;
  } else {
    dataObj.store_label_id = tabItem.goodsLabel.activeValue;
  }
  getProProduct(dataObj).then((res) => {
    tabItem.productList.list = res.data;
  });
}
</script>

<style scoped lang="scss">
.pro {
  padding: 15px 15px 0;
  .tips {
    height: 50px;
    line-height: 50px;
    color: #999;
    font-size: 12px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  }
}
.btn-box {
  padding-bottom: 20px;
}
</style>
