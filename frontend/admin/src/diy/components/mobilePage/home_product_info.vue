<template>
  <common_wrapper :config="configObj">
    <div class="product-info-box" :class="'style-' + specStyle">
      <div class="image-wrap">
        <img src="@/assets/images/product-diy.png" />
        <div class="indicators" :class="'pos-' + indicatorPosition" v-if="indicatorConfig">
          <!-- Line Style -->
          <div v-if="indicatorConfig.tabVal === 0" class="indicator-line">
            <div class="line-item active" :style="{ backgroundColor: selectColor }"></div>
            <div class="line-item" v-for="i in 4" :key="i" :style="{ backgroundColor: defaultColor }"></div>
          </div>
          <!-- Dot Style -->
          <div v-if="indicatorConfig.tabVal === 1" class="indicator-dot">
            <div class="dot-item active" :style="{ backgroundColor: selectColor }"></div>
            <div class="dot-item" :style="{ backgroundColor: defaultColor }"></div>
            <div class="dot-item" :style="{ backgroundColor: defaultColor }"></div>
          </div>
          <!-- Number Style -->
          <div v-if="indicatorConfig.tabVal === 2" class="indicator-number">
            <div class="num-box"><span class="current">1</span>/<span class="total">3</span></div>
          </div>
        </div>
        <!-- Spec Style 4: Below indicator, above card (inside image wrap) -->
        <div v-if="specStyle === 3" class="spec-style-4">
          <div class="spec-list">
            <div
              class="spec-item"
              v-for="(item, index) in mockSpecList"
              :key="index"
              :style="{
                borderColor: index === 0 ? specSelectedBorderColor : 'transparent',
                background: index === 0 ? specSelectedBgColor : '#777777',
              }"
            >
              <img :src="item.image" />
              <div
                class="spec-info"
                :style="{
                  color: index === 0 ? specSelectedTextColor : specUnselectedTextColor,
                }"
              >
                <div class="name">蓝色云朵蕾丝花边</div>
              </div>
            </div>
          </div>
          <div class="total-count" :style="{ color: specTextColor }">
            6款<br />可选<span class="iconfont iconyou"></span>
          </div>
        </div>
      </div>
      <div class="info-box" :style="{ background: infoBoxBg }">
        <!-- Spec Style 1 & 2: Top of info box -->
        <div v-if="specStyle === 0 || specStyle === 1" class="spec-top-section" :class="'style-' + specStyle">
          <div class="spec-list" v-if="specStyle === 0">
            <!-- Style 1: Small images -->
            <div class="spec-item" v-for="(item, index) in mockSpecList" :key="index">
              <img :src="item.image" :style="{ borderColor: index === 0 ? specSelectedBorderColor : '#eee' }" />
            </div>
            <div class="total-count" style="margin-left: auto" :style="{ color: specTextColor }">
              6款<br />可选<span class="iconfont iconyou"></span>
            </div>
          </div>
          <div class="spec-list-text" v-if="specStyle === 1">
            <!-- Style 2: Text/Image horizontal -->
            <div
              class="spec-item active"
              :style="{ background: specSelectedBgColor, borderColor: specSelectedBorderColor }"
            >
              <img :src="mockSpecList[0].image" />
              <span class="name" :style="{ color: specSelectedTextColor }">家庭时光-32P 彩色磁力积木</span>
            </div>
            <div class="spec-item">
              <img :src="mockSpecList[1].image" />
              <span class="name" :style="{ color: specUnselectedTextColor }">家庭时光-64P 彩色</span>
            </div>
            <div class="total-count" style="margin-left: auto" :style="{ color: specTextColor }">
              共6款<span class="iconfont iconyou"></span>
            </div>
          </div>
        </div>

        <div class="info-item" v-for="(item, index) in sortList" :key="index">
          <!-- Price Section -->
          <div v-if="item.name === 'price' && item.show" class="price-section">
            <div class="price-row">
              <div v-if="item.checkList.includes(0)" class="main-price-wrap" :style="{ color: finalPriceColor }">
                <span class="label">到手价</span>
                <span class="price" :style="{ fontSize: priceFontSize + 'px' }">¥199.00</span>
              </div>
              <div v-if="item.checkList.includes(1)" class="ot-price-wrap" :style="{ color: sellingPriceColor }">
                <span class="label">售价</span>
                <span class="price">¥299.00</span>
              </div>
              <div v-if="item.checkList.includes(2)" class="vip-price-wrap">
                <span class="badge">SVIP</span>
                <span class="price">¥26.00</span>
              </div>
            </div>
          </div>

          <!-- Name Section -->
          <div v-if="item.name === 'name' && item.show" class="name-section">
            <div class="title" :style="{ color: titleColor, fontSize: titleFontSize + 'px' }">
              美的（Midea）电热水壶家用烧水壶小容量 0涂层 食品级304不锈钢 双层防烫 全钢无缝
            </div>
          </div>

          <!-- Data Section -->
          <div v-if="item.name === 'data' && item.show" class="data-section">
            <span
              v-if="item.checkList.includes(0)"
              :style="{ color: originalPriceColor, textDecoration: 'line-through' }"
              >划线价: ¥299</span
            >
            <span v-if="item.checkList.includes(1)" :style="{ color: stockColor }">库存: 1000</span>
            <span v-if="item.checkList.includes(2)" :style="{ color: salesColor }">销量: 1000+</span>
          </div>

          <!-- Tags Section -->
          <div v-if="item.name === 'tags' && item.show" class="tags-section">
            <span class="tag">活动标签</span>
            <span class="tag">商品标签</span>
          </div>
        </div>

        <!-- Spec Style 3: Bottom of info box -->
        <div v-if="specStyle === 2" class="spec-bottom-section">
          <div class="spec-list">
            <div class="spec-item selected" :style="{ borderColor: specSelectedBorderColor }">
              <img :src="mockSpecList[0].image" />
              <div class="name" :style="{ color: specSelectedTextColor, background: specSelectedBgColor }">
                蓝色云朵
              </div>
            </div>
            <div class="spec-item" v-for="(item, index) in mockSpecList.slice(1)" :key="index">
              <img :src="item.image" />
              <div class="name" :style="{ color: specUnselectedTextColor }">黄色小鸭</div>
            </div>
            <div class="total-count" style="margin-left: auto">6款<br />可选<span class="iconfont iconyou"></span></div>
          </div>
        </div>
      </div>
    </div>
  </common_wrapper>
</template>

<script>
// 模块级具名导出：DIY 编辑器新增组件时经聚合层取默认配置工厂
// （原 Vue2 时代通过组件选项 data().defaultConfig 暴露；timestamp 依赖实例 props，故为工厂函数）
export const getDefaultConfig = (num) => ({
  cname: '商品信息',
  desc: '商品信息组件',
  name: 'productInfo',
  titleCurrency: '通用样式',
  timestamp: num,
  setUp: {
    tabVal: 0,
  },
  indicatorConfig: {
    title: '指示器设置',
    tabVal: 1, // 0: Line, 1: Dot-Line, 2: Number
    tabList: [{ name: '线段样式' }, { name: '点线样式' }, { name: '数字样式' }],
    positionVal: 1, // 0: Left, 1: Center, 2: Right
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
      tabVal: 0, // 0: Follow Theme, 1: Custom
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
        checkList: [1, 2],
        checkBoxList: [
          { name: '划线价', value: 0 },
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
  bottomBgColor: {
    title: '底部背景',
    default: [{ item: '#fff' }],
    color: [{ item: '#fff' }],
  },
  paddingConfig: {
    title: '内边距',
    val: 0,
    min: 0,
    max: 100,
    isAll: false,
    valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
  },
  marginConfig: {
    title: '外边距',
    val: 0,
    min: 0,
    max: 100,
    isAll: false,
    valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
  },
  zIndexConfig: {
    title: '组件上浮',
    val: 0,
    min: 0,
  },
  borderConfig: {
    title: '边框设置',
    tabVal: 0,
    tabList: [{ name: '隐藏' }, { name: '显示' }],
    val: 0, // 0: Hide, 1: Show
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
  },
  shadowConfig: {
    title: '阴影设置',
    tabVal: 0,
    tabList: [{ name: '隐藏' }, { name: '显示' }],
    val: 0, // 0: Off, 1: On
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
  },
  componentBgConfig: {
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
  },
  fillet: {
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
    val: 8,
    min: 0,
    valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
  },
});
</script>

<script setup>
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import { useMobildConfigStore } from '@diy/store/mobildConfig';
import __asset_product_diy_0 from '@/assets/images/product-diy.png';

defineOptions({
  name: 'home_product_info',
  cname: '商品信息',
  desc: '商品信息组件',
  configName: 'c_product_info',
  icon: '#iconzujian-shangpinxinxi',
  type: 3,
  // Product Component
  defaultName: 'productInfo',
});

const props = defineProps({
  index: {
    type: null,
  },
  num: {
    type: null,
  },
  colorStyle: {
    type: null,
  },
});
// 实例级默认配置（timestamp 取当前实例 num）
const defaultConfig = getDefaultConfig(props.num);

const mobildConfigStore = useMobildConfigStore();

const specStyle = computed(() => {
  return configObj.value.specStyle ? configObj.value.specStyle.tabVal : 0;
});

const specSettings = computed(() => {
  return configObj.value.specSettings || {};
});

const isCustomSpecTone = computed(() => {
  return specSettings.value.colorTone && specSettings.value.colorTone.tabVal === 1;
});

const specTextColor = computed(() => {
  if (isCustomSpecTone.value) {
    return specSettings.value.textColor && specSettings.value.textColor.color[0].item
      ? specSettings.value.textColor.color[0].item
      : '#E93323';
  }
  return props.colorStyle.theme || '#E93323';
});

const specSelectedBorderColor = computed(() => {
  if (isCustomSpecTone.value) {
    return specSettings.value.selectedBorderColor && specSettings.value.selectedBorderColor.color[0].item
      ? specSettings.value.selectedBorderColor.color[0].item
      : '#E93323';
  }
  return '#E93323';
});

const specSelectedTextColor = computed(() => {
  if (isCustomSpecTone.value) {
    return specSettings.value.selectedTextColor && specSettings.value.selectedTextColor.color[0].item
      ? specSettings.value.selectedTextColor.color[0].item
      : specTextColor.value;
  }
  return specTextColor.value;
});

const specSelectedBgColor = computed(() => {
  if (isCustomSpecTone.value) {
    return specSettings.value.selectedBgColor && specSettings.value.selectedBgColor.color[0].item
      ? specSettings.value.selectedBgColor.color[0].item
      : hexToRgba(specSelectedBorderColor.value, 0.1);
  }
  return specStyle.value === 3 ? '#777777' : hexToRgba(specSelectedBorderColor.value, 0.1);
});

const specUnselectedTextColor = computed(() => {
  if (isCustomSpecTone.value) {
    return specSettings.value.unselectedTextColor && specSettings.value.unselectedTextColor.color[0].item
      ? specSettings.value.unselectedTextColor.color[0].item
      : specStyle.value === 3
      ? '#ffffff'
      : '#333333';
  }
  return specStyle.value === 3 ? '#ffffff' : '#333333';
});

const titleColor = computed(() => {
  const config = configObj.value.titleConfig;
  if (!config) return '#333333';
  if (config.tabVal === 0) {
    return props.colorStyle.theme || '#333333';
  }
  return config.color && config.color.color[0].item ? config.color.color[0].item : '#333333';
});

const titleFontSize = computed(() => {
  return configObj.value.titleConfig && configObj.value.titleConfig.fontSize
    ? configObj.value.titleConfig.fontSize.val
    : 16;
});

const sortList = computed(() => {
  return configObj.value.sortList ? configObj.value.sortList.list : [];
});

const indicatorConfig = computed(() => {
  return configObj.value.indicatorConfig || {};
});

const selectColor = computed(() => {
  return indicatorConfig.value.selectColor ? indicatorConfig.value.selectColor.color[0].item : '#E93323';
});

const defaultColor = computed(() => {
  return indicatorConfig.value.defaultColor ? indicatorConfig.value.defaultColor.color[0].item : '#CCCCCC';
});

const indicatorPosition = computed(() => {
  if (indicatorConfig.value.tabVal === 0) return 'center'; // Line style always centered
  const pos = indicatorConfig.value.positionVal;
  return pos === 0 ? 'left' : pos === 2 ? 'right' : 'center';
});

const infoBoxBg = computed(() => {
  const componentBgConfig = configObj.value.componentBgConfig;
  const colorConfig = componentBgConfig && componentBgConfig.colorConfig;
  const colors = colorConfig && colorConfig.color ? colorConfig.color.map((item) => item.item || '#ffffff') : [];
  if (!colors.length) return 'linear-gradient(180deg, #ffffff 0%, #ffffff 100%)';
  if (colors.length === 1) return colors[0];
  const directionMap = ['90deg', '180deg', '135deg', '200deg'];
  const direction = componentBgConfig && componentBgConfig.colorDirection ? componentBgConfig.colorDirection.tabVal : 1;
  return `linear-gradient(${directionMap[direction] || '180deg'}, ${colors[0]} 0%, ${colors[1]} 100%)`;
});

const priceSettings = computed(() => {
  return configObj.value.priceSettings || {};
});

const dataSettings = computed(() => {
  return configObj.value.dataSettings || {};
});

const isCustomPriceTone = computed(() => {
  return priceSettings.value.colorTone && priceSettings.value.colorTone.tabVal === 1;
});

const finalPriceColor = computed(() => {
  if (isCustomPriceTone.value) {
    return priceSettings.value.finalPriceColor && priceSettings.value.finalPriceColor.color[0].item
      ? priceSettings.value.finalPriceColor.color[0].item
      : '#E93323';
  }
  return props.colorStyle.theme || '#E93323';
});

const sellingPriceColor = computed(() => {
  if (isCustomPriceTone.value) {
    return priceSettings.value.sellingPriceColor && priceSettings.value.sellingPriceColor.color[0].item
      ? priceSettings.value.sellingPriceColor.color[0].item
      : '#333333';
  }
  return '#333333';
});

const priceFontSize = computed(() => {
  return priceSettings.value.priceFontSize ? priceSettings.value.priceFontSize.val : 24;
});

const originalPriceColor = computed(() => {
  return dataSettings.value.originalPriceColor && dataSettings.value.originalPriceColor.color[0].item
    ? dataSettings.value.originalPriceColor.color[0].item
    : '#999999';
});

const stockColor = computed(() => {
  return dataSettings.value.stockColor && dataSettings.value.stockColor.color[0].item
    ? dataSettings.value.stockColor.color[0].item
    : '#999999';
});

const salesColor = computed(() => {
  return dataSettings.value.salesColor && dataSettings.value.salesColor.color[0].item
    ? dataSettings.value.salesColor.color[0].item
    : '#999999';
});

const configObj = ref({});
const pageData = ref({});
const mockSpecList = ref([
  { image: __asset_product_diy_0, name: '规格1' },
  { image: __asset_product_diy_0, name: '规格2' },
  { image: __asset_product_diy_0, name: '规格3' },
  { image: __asset_product_diy_0, name: '规格4' },
  { image: __asset_product_diy_0, name: '规格5' },
  { image: __asset_product_diy_0, name: '规格6' },
]);

watch(
  pageData,
  (nVal, oVal) => {
    setConfig(nVal);
  },
  { deep: true },
);
watch(
  () => props.num,
  (nVal, oVal) => {
    let data = mobildConfigStore.defaultArray[nVal];
    setConfig(data);
  },
  { deep: true },
);
watch(
  () => mobildConfigStore.defaultArray,
  (nVal, oVal) => {
    let data = mobildConfigStore.defaultArray[props.num];
    setConfig(data);
  },
  { deep: true },
);

onMounted(() => {
  nextTick(() => {
    pageData.value = mobildConfigStore.defaultArray[props.num];
    setConfig(pageData.value);
  });
});

function setConfig(data) {
  if (!data) return;
  if (data) {
    for (let key in defaultConfig) {
      if (data[key] === undefined) {
        data[key] = defaultConfig[key];
      }
    }
    configObj.value = data;
  }
}

function hexToRgba(hex, opacity) {
  if (!hex) return '';
  let c;
  if (/^#([A-Fa-f0-9]{3}){1,2}$/.test(hex)) {
    c = hex.substring(1).split('');
    if (c.length == 3) {
      c = [c[0], c[0], c[1], c[1], c[2], c[2]];
    }
    c = '0x' + c.join('');
    return 'rgba(' + [(c >> 16) & 255, (c >> 8) & 255, c & 255].join(',') + ',' + opacity + ')';
  }
  return hex;
}
</script>

<style scoped lang="scss">
.product-info-box {
  &.style-3 {
    .image-wrap .indicators {
      bottom: 85px;
    }
  }
  .image-wrap {
    position: relative;
    img {
      width: 100%;
      display: block;
    }
    .indicators {
      position: absolute;
      bottom: 30px;
      width: 100%;
      display: flex;
      padding: 0 10px;
      box-sizing: border-box;

      &.pos-left {
        justify-content: flex-start;
      }
      &.pos-center {
        justify-content: center;
      }
      &.pos-right {
        justify-content: flex-end;
      }

      .indicator-line {
        display: flex;
        align-items: center;
        width: 100%;
        .line-item {
          width: 20%;
          height: 2px;
          margin: 0 3px;
          border-radius: 2px;
        }
      }

      .indicator-dot {
        display: flex;
        align-items: center;
        .dot-item {
          width: 6px;
          height: 6px;
          border-radius: 50%;
          margin: 0 3px;
          transition: all 0.3s;
          &.active {
            width: 12px;
            border-radius: 3px;
          }
        }
      }

      .indicator-number {
        .num-box {
          background: rgba(0, 0, 0, 0.3);
          color: #fff;
          font-size: 12px;
          padding: 2px 8px;
          border-radius: 10px;
          .current {
            font-size: 14px;
          }
        }
      }
    }
  }

  .info-box {
    position: sticky;
    padding: 16px 16px 8px 16px;
    border-radius: 16px 16px 0 0;
    background: linear-gradient(180deg, #ffffff 0%, #ffffff 54%, rgba(255, 255, 255, 0) 100%);
    margin-top: -16px;
    z-index: 1;

    .spec-top-section {
      margin-bottom: 15px;
      &.style-0 {
        .spec-list {
          display: flex;
          align-items: center;
          overflow-x: auto;
          &::-webkit-scrollbar {
            display: none;
          }
          scrollbar-width: none;
          .spec-item {
            margin-right: 10px;
            flex-shrink: 0;
            img {
              width: 40px;
              height: 40px;
              border-radius: 4px;
              object-fit: cover;
              border: 1px solid #eee;
            }
            &:first-child img {
              border: 1px solid #e93323;
            }
          }
          .total-count {
            font-size: 12px;
            color: #999;
            line-height: 1.2;
            display: flex;
            align-items: center;
            flex-shrink: 0;
            position: sticky;
            right: 0;
            background-color: #fff;
            padding-left: 10px;
            z-index: 10;
            .iconfont {
              font-size: 12px;
            }
          }
        }
      }
      &.style-1 {
        .spec-list-text {
          display: flex;
          align-items: center;
          overflow-x: auto;
          &::-webkit-scrollbar {
            display: none;
          }
          scrollbar-width: none;
          .spec-item {
            display: flex;
            align-items: center;
            background: #f5f5f5;
            padding: 4px 8px;
            border-radius: 4px;
            margin-right: 10px;
            white-space: nowrap;
            flex-shrink: 0;
            img {
              width: 20px;
              height: 20px;
              margin-right: 5px;
              border-radius: 2px;
            }
            .name {
              font-size: 12px;
              color: #333;
            }
            &.active {
              background: #fdebeb;
              border: 1px solid #e93323;
              .name {
                color: #e93323;
              }
            }
          }
          .total-count {
            font-size: 12px;
            color: #999;
            height: 28px;
            display: flex;
            align-items: center;
            white-space: nowrap;
            flex-shrink: 0;
            position: sticky;
            right: 0;
            background-color: #fff;
            padding-left: 10px;
            z-index: 10;
            .iconfont {
              font-size: 12px;
            }
          }
        }
      }
    }

    .spec-bottom-section {
      margin-top: 15px;
      .spec-list {
        display: flex;
        overflow-x: auto;
        &::-webkit-scrollbar {
          display: none;
        }
        scrollbar-width: none;
        .spec-item {
          margin-right: 10px;
          text-align: center;
          flex-shrink: 0;
          img {
            width: 60px;
            height: 60px;
            border-radius: 6px;
            object-fit: cover;
          }
          .name {
            font-size: 12px;
            color: #333;
            margin-top: 5px;
          }
          &.selected {
            border: 1px solid #e93323;
            border-radius: 6px;
            overflow: hidden;

            .name {
              color: #e93323;
              background: #fdebeb;
              border-radius: 2px;
            }
          }
        }
        .total-count {
          display: flex;
          align-items: center;
          font-size: 12px;
          color: #999;
          flex-shrink: 0;
          position: sticky;
          right: 0;
          background-color: #fff;
          padding-left: 10px;
          z-index: 10;
          .iconfont {
            font-size: 12px;
          }
        }
      }
    }
  }

  .spec-style-4 {
    position: absolute;
    bottom: 5px;
    width: 100%;
    background: rgba(153, 153, 153, 0.6);
    padding: 10px 0px 15px 10px;
    box-sizing: border-box;
    overflow-x: hidden;
    display: flex;
    .spec-list {
      display: flex;
      align-items: center;
      flex: 1;
      min-width: 0;
      overflow-x: auto;
      overflow-y: hidden;
      .spec-item {
        flex-shrink: 0;
        margin-right: 10px;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #777777;
        padding: 2px;
        border-radius: 8px;
        img {
          width: 40px;
          height: 40px;
          border-radius: 4px;
        }
        .spec-info {
          display: flex;
          color: #fff;
          width: 60px;
          font-size: 12px;
          padding-left: 5px;
        }
        &:first-child {
          border: 1px solid #e93323;
          padding: 2px;
        }
      }
    }
    .total-count {
      flex-shrink: 0;
      margin-left: 5px;
      color: #fff;
      height: 44px;
      background: #777777;
      padding: 2px 2px 2px 10px;
      font-size: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      width: 60px;
      z-index: 10;
      border-radius: 4px 0 0 4px;
      .iconfont {
        font-size: 12px;
      }
    }
  }

  .info-item {
    margin-bottom: 8px;
  }

  .price-section {
    .price-row {
      display: flex;
      align-items: flex-end;
      flex-wrap: wrap;

      .main-price-wrap {
        display: flex;
        align-items: flex-end;
        margin-right: 10px;
        color: #e93323;
        .label {
          font-size: 11px;
          margin-right: 2px;
        }
        .price {
          font-size: 24px;
          font-weight: bold;
          line-height: 1;
        }
      }

      .ot-price-wrap {
        display: flex;
        align-items: flex-end;
        margin-right: 10px;
        color: #333;
        .label {
          font-size: 11px;
        }
        .price {
          font-size: 11px;
        }
      }

      .vip-price-wrap {
        display: flex;
        align-items: center;
        height: 18px;
        .badge {
          background: #333;
          color: #f8dcae;
          font-size: 10px;
          padding: 0 4px;
          border-radius: 9px 0 0 9px;
          height: 100%;
          display: flex;
          align-items: center;
          font-weight: bold;
        }
        .price {
          background: #fff0d8;
          color: #333;
          font-size: 11px;
          padding: 0 5px 0 2px;
          border-radius: 0 9px 9px 0;
          height: 100%;
          display: flex;
          align-items: center;
          font-weight: bold;
        }
      }
    }
  }

  .name-section {
    .title {
      font-size: 16px;
      color: #333;
      font-weight: bold;
      line-height: 1.4;
    }
  }

  .data-section {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    color: #999;
  }

  .tags-section {
    display: flex;
    flex-wrap: wrap;
    .tag {
      font-size: 10px;
      color: #e93323;
      border: 1px solid #e93323;
      padding: 0 4px;
      border-radius: 2px;
      margin-right: 5px;
    }
  }
}
</style>
