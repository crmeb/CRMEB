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
import { getCategory, getProProduct } from '@diy/api';
import toolCom from '@diy/components/mobileConfigRight/index.js';
import rightBtn from '@diy/components/rightBtn/index.vue';
import { useMobildConfigStore } from '@diy/store/mobildConfig';

defineOptions({ name: 'c_good_recommend', cname: '优品推荐', componentsName: 'home_good_recommend' });
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
let setUp = 0;
watch(
  () => props.num,
  (nVal) => {
    let value = JSON.parse(JSON.stringify(mobildConfigStore.defaultArray[nVal]));
    configObj.value = patchConfig(value);
    setUp = value.setUp.tabVal;
    updateRCom();
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
    updateRCom();
  },
  { deep: true },
);
watch(
  () => configObj.value?.headerType?.tabVal,
  (nVal, oVal) => {
    updateRCom();
  },
  { deep: true },
);
watch(
  () => configObj.value?.typeConfig?.activeValue,
  (nVal, oVal) => {
    updateRCom();
  },
  { deep: true },
);
watch(
  () => configObj.value?.cartConfig?.tabVal,
  (nVal, oVal) => {
    updateRCom();
  },
  { deep: true },
);
watch(
  () => configObj.value?.toneConfig?.tabVal,
  (nVal, oVal) => {
    updateRCom();
  },
  { deep: true },
);
watch(
  () => configObj.value?.toneCartConfig?.tabVal,
  (nVal, oVal) => {
    updateRCom();
  },
  { deep: true },
);
onMounted(() => {
  nextTick(() => {
    let value = JSON.parse(JSON.stringify(mobildConfigStore.defaultArray[props.num]));
    configObj.value = patchConfig(value);
    setUp = value.setUp.tabVal;
    updateRCom();
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
  if (!data.c_common_style) {
    data['c_common_style'] = {
      color: {
        title: '背景颜色',
        val: '',
        name: 'bgColor',
      },
      color2: {
        title: '线条颜色',
        val: '',
        name: 'lineColor',
      },
      lr: {
        title: '左右边距',
        val: 0,
        min: 0,
        max: 100,
      },
      type: 0,
    };
  }
  return data;
}
function updateRCom() {
  if (!configObj.value.setUp) return;

  let arr = [
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
    // Content Config
    let contentArr = [
      {
        components: toolCom.c_title,
        configNme: 'headerTitle',
      },
      {
        components: toolCom.c_radio,
        configNme: 'headerType',
      },
    ];

    if (configObj.value.headerType && configObj.value.headerType.tabVal == 0) {
      // Text
      contentArr.push({
        components: toolCom.c_input_item,
        configNme: 'headerText',
      });
    } else {
      // Image
      contentArr.push({
        components: toolCom.c_upload_img,
        configNme: 'headerImg',
      });
    }

    contentArr = contentArr.concat([
      {
        components: toolCom.c_title,
        configNme: 'titleGoods',
      },
      {
        components: toolCom.c_select,
        configNme: 'typeConfig',
      },
    ]);

    let type = configObj.value.typeConfig.activeValue;
    if (type == 1) {
      contentArr.push({
        components: toolCom.c_goods,
        configNme: 'goodsList',
      });
    } else if (type == 3) {
      contentArr.push({
        components: toolCom.c_classify,
        configNme: 'classList',
      });
      contentArr.push({
        components: toolCom.c_slider,
        configNme: 'numberConfig',
      });
      contentArr.push({
        components: toolCom.c_radio,
        configNme: 'goodsSort',
      });
    } else if (type == 4) {
      contentArr.push({
        components: toolCom.c_goods_label,
        configNme: 'goodsLabel',
      });
      contentArr.push({
        components: toolCom.c_slider,
        configNme: 'numberConfig',
      });
      contentArr.push({
        components: toolCom.c_radio,
        configNme: 'goodsSort',
      });
    }

    contentArr = contentArr.concat([
      {
        components: toolCom.c_checkbox,
        configNme: 'checkboxInfo',
      },
      {
        components: toolCom.c_radio,
        configNme: 'cartConfig',
      },
    ]);

    if (configObj.value.cartConfig.tabVal == 0) {
      contentArr.push({
        components: toolCom.c_button_img,
        configNme: 'bntStyleConfig',
      });
      contentArr.push({
        components: toolCom.c_radio,
        configNme: 'bntConfig',
      });
    }

    rCom.value = arr.concat(contentArr);
  } else {
    // Style Config
    let styleArr = [
      {
        components: toolCom.c_title,
        configNme: 'headerStyleTitle',
      },
      {
        components: toolCom.c_radio,
        configNme: 'headerAlign',
      },
    ];

    if (configObj.value.headerType.tabVal == 0) {
      styleArr.push({
        components: toolCom.c_radio,
        configNme: 'headerTextConfig', // Font Style (Bold/Normal/Italic)
      });
      styleArr.push({
        components: toolCom.c_bg_color,
        configNme: 'headerColor',
      });
      styleArr.push({
        components: toolCom.c_slider,
        configNme: 'headerFontSize',
      });
    }

    styleArr = styleArr.concat([
      {
        components: toolCom.c_radio,
        configNme: 'goodsName',
      },
      {
        components: toolCom.c_radio,
        configNme: 'toneConfig',
      },
    ]);

    if (configObj.value.toneConfig.tabVal == 1) {
      styleArr.push({
        components: toolCom.c_bg_color,
        configNme: 'goodsNameColor',
      });
      styleArr.push({
        components: toolCom.c_bg_color,
        configNme: 'goodsPriceColor',
      });
      styleArr.push({
        components: toolCom.c_bg_color,
        configNme: 'soldNumColor',
      });
      styleArr.push({
        components: toolCom.c_bg_color,
        configNme: 'scoreColor',
      });
    }

    if (configObj.value.cartConfig.tabVal == 0) {
      styleArr.push({
        components: toolCom.c_title,
        configNme: 'cartStyleTitle',
      });
      styleArr.push({
        components: toolCom.c_radio,
        configNme: 'toneCartConfig',
      });
      if (configObj.value.toneCartConfig.tabVal == 1) {
        styleArr.push({
          components: toolCom.c_bg_color,
          configNme: 'bntBgColor',
        });
      }
    }

    styleArr = styleArr.concat([
      {
        components: toolCom.c_title,
        configNme: 'goodsStyleTitle',
      },
      {
        components: toolCom.c_fillet,
        configNme: 'filletImg',
      },
      {
        components: toolCom.c_title,
        configNme: 'generalStyleTitle',
      },
      {
        components: toolCom.c_common_style,
        configNme: 'c_common_style',
      },
    ]);

    rCom.value = arr.concat(styleArr);
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
