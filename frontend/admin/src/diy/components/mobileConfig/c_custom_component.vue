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

    <!-- <rightBtn :activeIndex="activeIndex" :configObj="configObj"></rightBtn> -->
    <el-dialog
      custom-class="custom-design-dialog"
      v-model="modals"
      width="100%"
      fullscreen
      :close-on-click-modal="false"
      :show-close="false"
    >
      <CustomDesign
        v-if="modals"
        :initialData="configObj.customComponents"
        :columnNum="columnNum"
        :type="type"
        @save="handleSave"
        @close="modals = false"
      />
    </el-dialog>
  </div>
</template>
<script setup>
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import toolCom from '@diy/components/mobileConfigRight/index.js';
import rightBtn from '@diy/components/rightBtn/index.vue';
import CustomDesign from '@diy/components/CustomDesign';
import { useMobildConfigStore } from '@diy/store/mobildConfig';

defineOptions({ name: 'c_custom_component', componentsName: 'home_custom_component' });
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
const rCom = ref([]);
let setUp = 0;
const type = ref('user');
const modals = ref(false);
const columnNum = computed(() => {
  let styleConfig = null;
  if (type.value === 'article') styleConfig = configObj.value.articleColumnStyle;
  else if (type.value === 'goods') styleConfig = configObj.value.goodsColumnStyle;
  else if (type.value === 'coupon') styleConfig = configObj.value.couponColumnStyle;

  if (styleConfig) {
    return (styleConfig.tabVal || 0) + 1;
  }
  return 1;
});
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
    updateRCom();
  },
  { deep: true },
);
watch(
  () => configObj.value?.selectType?.activeValue,
  (nVal, oVal) => {
    type.value = nVal;
    updateRCom();
  },
  { deep: true },
);
watch(
  () => configObj.value?.couponUserType?.activeValue,
  (nVal, oVal) => {
    updateRCom();
  },
  { deep: true },
);
watch(
  () => configObj.value?.goodsDataSource?.tabVal,
  (nVal, oVal) => {
    updateRCom();
  },
  { deep: true },
);
watch(
  () => configObj.value?.couponDataSource?.tabVal,
  (nVal, oVal) => {
    updateRCom();
  },
  { deep: true },
);
watch(
  () => configObj.value?.couponThreshold?.tabVal,
  (nVal, oVal) => {
    updateRCom();
  },
  { deep: true },
);
watch(
  () => configObj.value?.articleDataSource?.tabVal,
  (nVal, oVal) => {
    updateRCom();
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
  // Ensure structure exists if missing
  if (!data.setUp) {
    data['setUp'] = { tabVal: 0 };
  }
  if (!data.customBtnConfig) {
    data['customBtnConfig'] = {
      title: '设计组件',
    };
  }
  if (!data.fillet) {
    data['fillet'] = {
      title: '背景圆角',
      type: 0,
      list: [
        {
          val: '全部',
          icon: 'iconcaozuo-zhengti',
        },
        {
          val: '单个',
          icon: 'iconcaozuo-bianjiao',
        },
      ],
      valName: '圆角值',
      val: 0,
      min: 0,
      valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
    };
  }
  if (!data.filletDataConfig) {
    data['filletDataConfig'] = {
      title: '背景圆角',
      type: 0,
      list: [
        {
          val: '全部',
          icon: 'iconcaozuo-zhengti',
        },
        {
          val: '单个',
          icon: 'iconcaozuo-bianjiao',
        },
      ],
      valName: '圆角值',
      val: 0,
      min: 0,
      valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
    };
  }
  if (!data.componentBgConfig) {
    data['componentBgConfig'] = {
      title: '背景样式',
      tabVal: 0,
      tabList: [{ name: '颜色' }, { name: '图片' }],
      colorConfig: {
        title: '背景颜色',
        color: [
          {
            item: '#fff',
          },
        ],
        default: [
          {
            item: '#fff',
          },
        ],
      },
      imageConfig: {
        title: '背景图片',
        url: '',
      },
      colorDirection: {
        title: '渐变方向',
        tabVal: 0,
        tabList: [{ name: '横向' }, { name: '纵向' }, { name: '左斜' }, { name: '右斜' }],
      },
    };
  }
  if (!data.componentBgDataConfig) {
    data['componentBgDataConfig'] = {
      title: '背景样式',
      tabVal: 0,
      tabList: [{ name: '颜色' }, { name: '图片' }],
      colorConfig: {
        title: '背景颜色',
        color: [
          {
            item: '#fff',
          },
        ],
        default: [
          {
            item: '#fff',
          },
        ],
      },
      imageConfig: {
        title: '背景图片',
        url: '',
      },
      colorDirection: {
        title: '渐变方向',
        tabVal: 0,
        tabList: [{ name: '横向' }, { name: '纵向' }, { name: '左斜' }, { name: '右斜' }],
      },
    };
  }
  if (!data.marginConfig) {
    data['marginConfig'] = {
      title: '边距设置',
      val: 0,
      min: 0,
      max: 100,
      isAll: false,
      valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
    };
  }
  if (!data.marginDataConfig) {
    data['marginDataConfig'] = {
      title: '外边距',
      val: 0,
      min: 0,
      max: 100,
      isAll: false,
      valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
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
  if (!data.paddingDataConfig) {
    data['paddingDataConfig'] = {
      title: '内边距',
      val: 0,
      min: 0,
      max: 100,
      isAll: false,
      valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
    };
  }
  if (!data.borderConfig) {
    data['borderConfig'] = {
      title: '边框设置',
      tabVal: 0,
      tabList: [
        {
          name: '隐藏',
        },
        {
          name: '显示',
        },
      ],
      val: 0,
      styleConfig: {
        title: '边框样式',
        tabVal: 0,
        tabList: [
          {
            name: '实线',
            style: 'solid',
          },
          {
            name: '虚线',
            style: 'dashed',
          },
          {
            name: '点状',
            style: 'dotted',
          },
        ],
      },
      widthConfig: {
        title: '边框粗细',
        val: 1,
        min: 1,
      },
      colorConfig: {
        title: '边框颜色',
        default: [
          {
            item: '#e5e5e5',
          },
        ],
        color: [
          {
            item: '#e5e5e5',
          },
        ],
      },
    };
  }
  if (!data.borderDataConfig) {
    data['borderDataConfig'] = {
      title: '边框设置',
      tabVal: 0,
      tabList: [
        {
          name: '隐藏',
        },
        {
          name: '显示',
        },
      ],
      val: 0,
      styleConfig: {
        title: '边框样式',
        tabVal: 0,
        tabList: [
          {
            name: '实线',
            style: 'solid',
          },
          {
            name: '虚线',
            style: 'dashed',
          },
          {
            name: '点状',
            style: 'dotted',
          },
        ],
      },
      widthConfig: {
        title: '边框粗细',
        val: 1,
        min: 1,
      },
      colorConfig: {
        title: '边框颜色',
        default: [
          {
            item: '#e5e5e5',
          },
        ],
        color: [
          {
            item: '#e5e5e5',
          },
        ],
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
  if (!data.shadowDataConfig) {
    data['shadowDataConfig'] = {
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
  if (!data.bottomBgColor) {
    data['bottomBgColor'] = {
      title: '底部背景',
      color: [
        {
          item: '#f5f5f5',
        },
      ],
      default: [
        {
          item: '#f5f5f5',
        },
      ],
    };
  }
  if (!data.selectType) {
    data['selectType'] = {
      title: '选择信息',
      activeValue: 'user',
      list: [
        { activeValue: 'user', title: '用户' },
        { activeValue: 'article', title: '文章' },
        { activeValue: 'coupon', title: '优惠券' },
        { activeValue: 'goods', title: '商品' },
      ],
    };
  }
  return data;
}
function updateRCom() {
  if (setUp === 0) {
    // Content Config
    let arr = [
      {
        components: toolCom.c_set_up,
        configNme: 'setUp',
      },
      {
        components: toolCom.c_title,
        configNme: 'messageTitle',
      },
      {
        components: toolCom.c_select,
        configNme: 'selectType',
      },
    ];

    if (type.value === 'article') {
      arr = arr.concat([
        { components: toolCom.c_radio, configNme: 'articleDisplayMode' },
        { components: toolCom.c_radio, configNme: 'articleColumnStyle' },
        {
          components: toolCom.c_title,
          configNme: 'dataTitle',
        },
        { components: toolCom.c_radio, configNme: 'articleDataSource' },
      ]);

      if (configObj.value.articleDataSource && configObj.value.articleDataSource.tabVal === 0) {
        // Specific Data
        arr.push({ components: toolCom.c_article, configNme: 'articleList' });
      } else if (configObj.value.articleDataSource && configObj.value.articleDataSource.tabVal === 1) {
        // Filtered Data
        arr.push(
          { components: toolCom.c_classify, configNme: 'articleClass' },
          { components: toolCom.c_input_number, configNme: 'articleNum' },
          { components: toolCom.c_radio, configNme: 'articleSort' },
          { components: toolCom.c_radio, configNme: 'articleSortRule' },
        );
        arr.push({ components: toolCom.c_input_number, configNme: 'articleNum' });
      }
    } else if (type.value === 'coupon') {
      arr = arr.concat([
        { components: toolCom.c_radio, configNme: 'couponDisplayMode' },
        { components: toolCom.c_radio, configNme: 'couponColumnStyle' },
        {
          components: toolCom.c_title,
          configNme: 'dataTitle',
        },
        { components: toolCom.c_radio, configNme: 'couponDataSource' },
      ]);

      if (configObj.value.couponDataSource && configObj.value.couponDataSource.tabVal === 0) {
        // Manual Selection
        arr.push({ components: toolCom.c_coupon_select, configNme: 'couponList' });
      } else if (configObj.value.couponDataSource && configObj.value.couponDataSource.tabVal === 1) {
        // Filtered Data
        arr.push(
          { components: toolCom.c_select, configNme: 'couponType' },
          { components: toolCom.c_select, configNme: 'couponUserType' },
        );
        if (configObj.value.couponUserType.activeValue != 2) {
          arr.push({ components: toolCom.c_select, configNme: 'couponSendType' });
        }
        arr.push({ components: toolCom.c_radio, configNme: 'couponThreshold' });
        if (configObj.value.couponThreshold && configObj.value.couponThreshold.tabVal === 1) {
          arr.push({ components: toolCom.c_input_number, configNme: 'couponThresholdValue' });
        }
        arr.push(
          { components: toolCom.c_datetime_picker, configNme: 'couponTime' },
          { components: toolCom.c_radio, configNme: 'couponSort' },
          { components: toolCom.c_radio, configNme: 'couponSortRule' },
        );
      }

      // arr.push({ components: toolCom.c_input_number, configNme: 'couponNum' });
    } else if (type.value === 'goods') {
      arr = arr.concat([
        { components: toolCom.c_radio, configNme: 'goodsDisplayMode' },
        { components: toolCom.c_radio, configNme: 'goodsColumnStyle' },
        {
          components: toolCom.c_title,
          configNme: 'dataTitle',
        },
        { components: toolCom.c_radio, configNme: 'goodsDataSource' },
      ]);

      if (configObj.value.goodsDataSource && configObj.value.goodsDataSource.tabVal === 0) {
        // Specific Data
        arr.push({ components: toolCom.c_goods, configNme: 'goodsList' });
      } else if (configObj.value.goodsDataSource && configObj.value.goodsDataSource.tabVal === 1) {
        // Category Data
        arr.push(
          { components: toolCom.c_classify, configNme: 'goodsClass' },
          { components: toolCom.c_radio, configNme: 'goodsSort' },
          { components: toolCom.c_radio, configNme: 'goodsSortRule' },
        );
        arr.push({ components: toolCom.c_input_number, configNme: 'goodsNum' });
      }
    }

    arr.push(
      {
        components: toolCom.c_title,
        configNme: 'designTitle',
      },
      { components: toolCom.c_custom_btn, configNme: 'customBtnConfig' },
    );

    rCom.value = arr;
  } else {
    // Style Config
    rCom.value = [
      {
        components: toolCom.c_set_up,
        configNme: 'setUp',
      },
      {
        components: toolCom.c_data_style,
        configNme: 'c_data_style',
      },
      {
        components: toolCom.c_title,
        configNme: 'commonTitle',
      },
      {
        components: toolCom.c_common_style,
        configNme: 'c_common_style',
      },
      // Can add padding/margin config here if c_slider/c_margin_style is available and configured
    ];
  }
}
function getConfig(data) {
  if (data?.name == 'custom_btn_click') {
    modals.value = true;
  }
}
function handleSave(data) {
  configObj.value['customComponents'] = data;
}
</script>

<style scoped lang="scss">
.mobile-config {
  padding-bottom: 20px;
}
:deep(.custom-design-dialog) {
  border-radius: 0px !important;
  .el-dialog__header {
    display: none;
  }
  .el-dialog__body {
    padding: 0 !important;
    height: 100%;
    overflow: hidden;
    max-height: 100vh !important;
  }
}
</style>
