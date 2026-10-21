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
        :number="num"
        :num="item.num"
      ></component>
    </div>
    <rightBtn :activeIndex="activeIndex" :configObj="configObj"></rightBtn>
  </div>
</template>
<script setup>
import { nextTick, onMounted, ref, watch } from 'vue';
import { getCategory as getCategoryApi, getProProduct } from '@diy/api';
import toolCom from '@diy/components/mobileConfigRight/index.js';
import rightBtn from '@diy/components/rightBtn/index.vue';
import { useMobildConfigStore } from '@diy/store/mobildConfig';

defineOptions({ name: 'c_home_goods_list', cname: '产品列表', componentsName: 'home_goods_list' });
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
    configNme: 'titleGoods',
  },
  {
    components: toolCom.c_select,
    configNme: 'typeConfig',
  },
];
const oneContent1 = [
  {
    components: toolCom.c_goods,
    configNme: 'goodsList',
  },
];
const oneContent2 = [
  {
    components: toolCom.c_brand,
    configNme: 'brandList',
  },
  {
    components: toolCom.c_slider,
    configNme: 'numberConfig',
  },
  {
    components: toolCom.c_radio,
    configNme: 'goodsSort',
  },
];
const oneContent3 = [
  {
    components: toolCom.c_classify,
    configNme: 'classList',
  },
  {
    components: toolCom.c_slider,
    configNme: 'numberConfig',
  },
  {
    components: toolCom.c_radio,
    configNme: 'goodsSort',
  },
];
const oneContent4 = [
  {
    components: toolCom.c_goods_label,
    configNme: 'goodsLabel',
  },
  {
    components: toolCom.c_slider,
    configNme: 'numberConfig',
  },
  {
    components: toolCom.c_radio,
    configNme: 'goodsSort',
  },
];
const twoContent = [
  {
    components: toolCom.c_title,
    configNme: 'titleContents',
  },
  {
    components: toolCom.c_checkbox,
    configNme: 'checkboxInfo',
  },
];
const threeContent = [
  {
    components: toolCom.c_title,
    configNme: 'titleCart',
  },
  {
    components: toolCom.c_radio,
    configNme: 'cartConfig',
  },
];
const threeContent1 = [
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
    components: toolCom.c_fillet,
    configNme: 'filletImg',
  },
  {
    components: toolCom.c_radio,
    configNme: 'goodsName',
  },
  {
    components: toolCom.c_radio,
    configNme: 'toneConfig',
  },
];
const oneStyle1 = [
  {
    components: toolCom.c_bg_color,
    configNme: 'goodsNameColor',
  },
  {
    components: toolCom.c_bg_color,
    configNme: 'goodsPriceColor',
  },
];
const oneStyle2 = [
  {
    components: toolCom.c_bg_color,
    configNme: 'soldNumColor',
  },
];
const oneStyle3 = [
  {
    components: toolCom.c_bg_color,
    configNme: 'scoreColor',
  },
];
const twoStyle = [
  {
    components: toolCom.c_title,
    configNme: 'titleCart',
  },
  {
    components: toolCom.c_radio,
    configNme: 'toneCartConfig',
  },
];
const twoStyle1 = [
  {
    components: toolCom.c_bg_color,
    configNme: 'bntBgColor',
  },
];
const currencyTitleStyle = [
  {
    components: toolCom.c_title,
    configNme: 'titleCurrency',
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
let type5 = 0;
const lockStatus = false;
watch(
  () => props.num,
  (nVal) => {
    let value = JSON.parse(JSON.stringify(mobildConfigStore.defaultArray[nVal]));
    configObj.value = patchConfig(value);
    if (!value.selectConfig.list || !value.selectConfig.list[0].value) {
      getCategory();
    }
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
      getRComContent(arr, type, type2, type3);
    } else {
      getRComStyle(arr, type, type3, type4, type5);
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
    if (setUp === 0) {
      getRComContent(arr, nVal, type2, type3);
    } else {
      getRComStyle(arr, nVal, type3, type4, type5);
    }
  },
  { deep: true },
);
watch(
  () => configObj.value?.typeConfig?.activeValue,
  (nVal, oVal) => {
    type2 = nVal;
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
    if (setUp === 0) {
      getRComContent(arr, type, nVal, type3);
    }
  },
  { deep: true },
);
watch(
  () => configObj.value?.cartConfig?.tabVal,
  (nVal, oVal) => {
    type3 = nVal;
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
    if (setUp === 0) {
      getRComContent(arr, type, type2, nVal);
    } else {
      getRComStyle(arr, type, nVal, type4, type5);
    }
  },
  { deep: true },
);
watch(
  () => configObj.value?.toneConfig?.tabVal,
  (nVal, oVal) => {
    type4 = nVal;
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
    if (setUp) {
      getRComStyle(arr, type, type3, nVal, type5);
    }
  },
  { deep: true },
);
watch(
  () => configObj.value?.toneCartConfig?.tabVal,
  (nVal, oVal) => {
    type5 = nVal;
    // arr 须与其余 watcher 保持一致（勿插入 titleLeft 标题）：
    // rCom 按 index 复用 key，此处多插一项会使后续控件全部错位重挂载，
    // 顶部「选择风格」卡片（c_card_select）会因无初始化而渲染为空白
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
    if (setUp) {
      getRComStyle(arr, type, type3, type4, nVal);
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
    data['marginConfig'] = {
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
function getCategory() {
  getCategoryApi().then((res) => {
    configObj.value.selectConfig['list'] = res.data;
  });
}
function getRComContent(arr, type, type2, type3) {
  if (type == 3) {
    if (type2 == 1) {
      rCom.value = [...arr, ...oneContent, ...oneContent1, ...twoContent];
    } else if (type2 == 2) {
      rCom.value = [...arr, ...oneContent, ...oneContent2, ...twoContent];
    } else if (type2 == 3) {
      rCom.value = [...arr, ...oneContent, ...oneContent3, ...twoContent];
    } else {
      rCom.value = [...arr, ...oneContent, ...oneContent4, ...twoContent];
    }
  } else {
    if (type2 == 1) {
      if (type3 == 0) {
        rCom.value = [...arr, ...oneContent, ...oneContent1, ...twoContent, ...threeContent, ...threeContent1];
      } else {
        rCom.value = [...arr, ...oneContent, ...oneContent1, ...twoContent, ...threeContent];
      }
    } else if (type2 == 2) {
      if (type3 == 0) {
        rCom.value = [...arr, ...oneContent, ...oneContent2, ...twoContent, ...threeContent, ...threeContent1];
      } else {
        rCom.value = [...arr, ...oneContent, ...oneContent2, ...twoContent, ...threeContent];
      }
    } else if (type2 == 3) {
      if (type3 == 0) {
        rCom.value = [...arr, ...oneContent, ...oneContent3, ...twoContent, ...threeContent, ...threeContent1];
      } else {
        rCom.value = [...arr, ...oneContent, ...oneContent3, ...twoContent, ...threeContent];
      }
    } else {
      if (type3 == 0) {
        rCom.value = [...arr, ...oneContent, ...oneContent4, ...twoContent, ...threeContent, ...threeContent1];
      } else {
        rCom.value = [...arr, ...oneContent, ...oneContent4, ...twoContent, ...threeContent];
      }
    }
  }
}
function getRComStyle(arr, type, type3, type4, type5) {
  let obj4 = [];
  // 通用样式取模块级 currencyStyle（原 Vue2 为 [...this.currencyStyle]）；
  // 勿在函数内再声明同名局部变量，否则遮蔽后自展开空数组，样式设置页会丢失整个通用样式区
  const commonStyle = [...currencyStyle];
  if (type4) {
    if (type == 1 || type == 4) {
      obj4 = [...oneStyle1, ...oneStyle2];
    } else if (type == 0) {
      obj4 = [...oneStyle1, ...oneStyle2, ...oneStyle3];
    } else if (type == 2 || type == 3) {
      obj4 = [...oneStyle1];
    } else {
      obj4 = [...oneStyle1, ...oneStyle2];
    }
  }
  let obj5 = [];
  if (type != 3) {
    if (type5) {
      obj5 = [...twoStyle, ...twoStyle1];
    } else {
      obj5 = [...twoStyle];
    }
  }
  if (type3 == 0) {
    rCom.value = [...arr, ...oneStyle, ...obj4, ...obj5, ...commonStyle];
  } else {
    rCom.value = [...arr, ...oneStyle, ...obj4, ...commonStyle];
  }
}
function getConfig(data, name) {
  // 切换样式时，选择方式为「指定商品」则保留已选商品，不清空 goodsList
  if (name != 'radio' && !data.name && data == 1 && configObj.value.typeConfig.activeValue != 1) {
    configObj.value.goodsList.list = [];
    return;
  }
  if (
    name != 'radio' &&
    !data.name &&
    data == 0 &&
    !configObj.value.classList.classVal.length &&
    configObj.value.typeConfig.activeValue != 1
  ) {
    configObj.value.goodsList.list = [];
    return;
  }
  // if( data.name=='radio'){
  //     return;
  // }
  let type = configObj.value.typeConfig.activeValue;
  let dataObj = {
    page: 1,
    limit: configObj.value.numberConfig.val,
    priceOrder: configObj.value.goodsSort.tabVal == 2 ? 'desc' : '',
    salesOrder: configObj.value.goodsSort.tabVal == 1 ? 'desc' : '',
  };
  if (type == 1) {
    configObj.value.productList.list = [];
    return;
  } else if (type == 3) {
    dataObj.cate_id = configObj.value.classList.classVal;
  } else {
    dataObj.store_label_id = configObj.value.goodsLabel.activeValue;
  }
  getProProduct(dataObj).then((res) => {
    configObj.value.productList.list = res.data;
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
