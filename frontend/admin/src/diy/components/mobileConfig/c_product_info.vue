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

defineOptions({ name: 'c_product_info', componentsName: 'home_product_info' });
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
const defaultConfig = {
  cname: '商品信息',
  desc: '商品信息组件',
  name: 'productInfo',
  timestamp: props.num,
  setUp: {
    tabVal: 0,
  },
  indicatorConfig: {
    title: '指示器设置',
    tabVal: 1,
    tabList: [{ name: '线段样式' }, { name: '点线样式' }, { name: '数字样式' }],
    positionVal: 1,
    positionList: [{ name: '左对齐' }, { name: '居中对齐' }, { name: '右对齐' }],
    selectColor: {
      title: '选中样式',
      name: 'selectColor',
      default: [{ item: '#E93323' }],
      color: [{ item: '#E93323' }],
    },
    defaultColor: {
      title: '默认样式',
      name: 'defaultColor',
      default: [{ item: '#CCCCCC' }],
      color: [{ item: '#CCCCCC' }],
    },
  },
  titleConfig: {
    title: '标题设置',
    tabVal: 0,
    tabList: [
      { name: '跟随主题风格', val: 0 },
      { name: '自定义', val: 1 },
    ],
    color: {
      title: '标题颜色',
      default: [{ item: '#333333' }],
      color: [{ item: '#333333' }],
    },
    fontSize: {
      title: '字体大小',
      val: 16,
      min: 12,
    },
  },
  specStyle: {
    title: '规格样式',
    tabVal: 0,
    tabList: [{ name: '样式一' }, { name: '样式二' }, { name: '样式三' }, { name: '样式四' }],
  },
  specSettings: {
    title: '规格设置',
    colorTone: {
      title: '色调',
      tabVal: 0,
      tabList: [
        { name: '跟随主题风格', val: 0 },
        { name: '自定义', val: 1 },
      ],
    },
    textColor: {
      title: '按钮颜色',
      name: 'textColor',
      default: [{ item: '#666' }],
      color: [{ item: '#666' }],
    },
    selectedBorderColor: {
      title: '选中边框',
      name: 'selectedBorderColor',
      default: [{ item: '#E93323' }],
      color: [{ item: '#E93323' }],
    },
    selectedTextColor: {
      title: '选中文字',
      name: 'selectedTextColor',
      default: [{ item: '#E93323' }],
      color: [{ item: '#E93323' }],
    },
    selectedBgColor: {
      title: '选中背景',
      name: 'selectedBgColor',
      default: [{ item: '#FDEBEB' }],
      color: [{ item: '#FDEBEB' }],
    },
    unselectedTextColor: {
      title: '未选中文字',
      name: 'unselectedTextColor',
      default: [{ item: '#333333' }],
      color: [{ item: '#333333' }],
    },
  },
  sortList: {
    title: '信息设置',
    tips: '鼠标拖拽板块可调整信息展示顺序',
    list: [
      {
        name: 'price',
        cname: '商品价格',
        type: 'radio',
        show: true,
        checkList: [0, 1, 2],
        checkBoxList: [
          { name: '到手价', value: 0 },
          { name: '售价', value: 1 },
          { name: '会员价', value: 2 },
        ],
      },
      {
        name: 'name',
        cname: '商品名称',
        type: 'radio',
        show: true,
      },
      {
        name: 'data',
        cname: '商品数据',
        type: 'radio',
        show: true,
        checkList: [0, 1, 2],
        checkBoxList: [
          { name: '原价', value: 0 },
          { name: '库存', value: 1 },
          { name: '销量', value: 2 },
        ],
      },
      {
        name: 'tags',
        cname: '商品标签',
        type: 'radio',
        show: true,
      },
    ],
  },
  priceSettings: {
    title: '价格设置',
    colorTone: {
      title: '色调',
      tabVal: 0,
      tabList: [
        { name: '跟随主题风格', val: 0 },
        { name: '自定义', val: 1 },
      ],
    },
    finalPriceColor: {
      title: '到手价颜色',
      name: 'finalPriceColor',
      default: [{ item: '#E93323' }],
      color: [{ item: '#E93323' }],
    },
    sellingPriceColor: {
      title: '售价颜色',
      name: 'sellingPriceColor',
      default: [{ item: '#333333' }],
      color: [{ item: '#333333' }],
    },
    priceFontSize: {
      title: '价格字号',
      val: 24,
      min: 12,
      max: 50,
    },
  },
  dataSettings: {
    title: '数据设置',
    originalPriceColor: {
      title: '原价颜色',
      name: 'originalPriceColor',
      default: [{ item: '#999999' }],
      color: [{ item: '#999999' }],
    },
    stockColor: {
      title: '库存颜色',
      name: 'stockColor',
      default: [{ item: '#999999' }],
      color: [{ item: '#999999' }],
    },
    salesColor: {
      title: '销量颜色',
      name: 'salesColor',
      default: [{ item: '#999999' }],
      color: [{ item: '#999999' }],
    },
  },
};
const rCom = ref([
  {
    components: toolCom.c_card_select,
    configNme: 'specStyle',
  },
  {
    components: toolCom.c_set_up,
    configNme: 'setUp',
  },
]);
watch(
  () => props.num,
  (nVal) => {
    let value = JSON.parse(JSON.stringify(mobildConfigStore.defaultArray[nVal]));
    setConfig(value);
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
  (nVal) => {
    setUpChange(nVal);
  },
);
onMounted(() => {
  nextTick(() => {
    let value = JSON.parse(JSON.stringify(mobildConfigStore.defaultArray[props.num]));
    setConfig(value);
    setUpChange(value.setUp.tabVal);
  });
});
function setConfig(data) {
  if (!data) return;
  patchConfig(data);
  for (let key in defaultConfig) {
    if (data[key] === undefined) {
      data[key] = defaultConfig[key];
    }
  }
  configObj.value = data;
}
function patchConfig(data) {
  if (!data.componentBgConfig) {
    data['componentBgConfig'] = {
      title: '背景设置',
      tabVal: 0,
      tabList: [{ name: '颜色' }, { name: '图片' }],
      colorConfig: {
        title: '背景颜色',
        default: [{ item: '#fff' }, { item: '#fff' }],
        color: [{ item: '#fff' }, { item: '#fff' }],
      },
      colorDirection: {
        title: '渐变方向',
        tabVal: 0,
        tabList: [{ name: '横向' }, { name: '纵向' }, { name: '左斜' }, { name: '右斜' }],
      },
      imageConfig: {
        header: '背景图片',
        title: '',
        name: '上传图片',
        type: 'code',
        url: '',
        info: '建议尺寸：750px * 400px',
      },
    };
  }
  if (!data.paddingConfig) {
    data['paddingConfig'] = {
      title: '内边距',
      val: 0,
      min: 0,
      max: 100,
      isAll: false,
      valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
    };
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
      title: '边框设置',
      tabVal: 0,
      tabList: [{ name: '隐藏' }, { name: '显示' }],
      val: 0,
      styleConfig: {
        title: '边框样式',
        tabVal: 0,
        tabList: [
          { name: '实线', style: 'solid' },
          { name: '虚线', style: 'dashed' },
          { name: '点状', style: 'dotted' },
        ],
      },
      widthConfig: {
        title: '边框粗细',
        val: 1,
        min: 1,
      },
      colorConfig: {
        title: '边框颜色',
        default: [{ item: '#e5e5e5' }],
        color: [{ item: '#e5e5e5' }],
      },
    };
  }
  if (!data.shadowConfig) {
    data['shadowConfig'] = {
      title: '阴影设置',
      tabVal: 0,
      tabList: [{ name: '隐藏' }, { name: '显示' }],
      val: 0,
      colorConfig: {
        title: '阴影颜色',
        default: [{ item: 'rgba(0,0,0,0.1)' }],
        color: [{ item: 'rgba(0,0,0,0.1)' }],
      },
      xConfig: {
        title: 'X轴偏移',
        val: 0,
        min: -50,
      },
      yConfig: {
        title: 'Y轴偏移',
        val: 0,
        min: -50,
      },
      blurConfig: {
        title: '模糊半径',
        val: 10,
        min: 0,
      },
      spreadConfig: {
        title: '扩展半径',
        val: 0,
        min: -50,
      },
    };
  }
  if (data.specSettings) {
    if (!data.specSettings.selectedBorderColor) {
      data.specSettings['selectedBorderColor'] = {
        title: '选中边框',
        name: 'selectedBorderColor',
        default: [{ item: '#E93323' }],
        color: [{ item: '#E93323' }],
      };
    }
    if (!data.specSettings.selectedTextColor) {
      data.specSettings['selectedTextColor'] = {
        title: '选中文字',
        name: 'selectedTextColor',
        default: [{ item: '#E93323' }],
        color: [{ item: '#E93323' }],
      };
    }
    if (!data.specSettings.selectedBgColor) {
      data.specSettings['selectedBgColor'] = {
        title: '选中背景',
        name: 'selectedBgColor',
        default: [{ item: '#FDEBEB' }],
        color: [{ item: '#FDEBEB' }],
      };
    }
    if (!data.specSettings.unselectedTextColor) {
      data.specSettings['unselectedTextColor'] = {
        title: '未选中文字',
        name: 'unselectedTextColor',
        default: [{ item: '#333333' }],
        color: [{ item: '#333333' }],
      };
    }
  }
}
function setUpChange(val) {
  rCom.value = [
    {
      components: toolCom.c_card_select,
      configNme: 'specStyle',
    },
    {
      components: toolCom.c_set_up,
      configNme: 'setUp',
    },
  ];
  if (val == 0) {
    // Content Settings
    // Spec Style at the top

    // Product Info List (Draggable Sections)
    rCom.value.push({
      components: toolCom.c_product_info_list,
      configNme: 'sortList',
    });
  } else {
    // Style Settings
    rCom.value.push({
      components: toolCom.c_indicator_settings,
      configNme: 'indicatorConfig',
    });
    rCom.value.push({
      components: toolCom.c_title_settings,
      configNme: 'titleConfig',
    });
    rCom.value.push({
      components: toolCom.c_spec_settings,
      configNme: 'specSettings',
    });
    rCom.value.push({
      components: toolCom.c_price_settings,
      configNme: 'priceSettings',
    });
    rCom.value.push({
      components: toolCom.c_data_settings,
      configNme: 'dataSettings',
    });
    rCom.value.push({
      components: toolCom.c_common_style,
      configNme: 'c_common_style',
    });
  }
}
function getConfig(data) {
  // Handle updates from child components if needed
}
</script>

<style scoped lang="scss"></style>
