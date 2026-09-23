<template>
  <common_wrapper :config="configObj">
    <div class="home_product">
      <!-- Header Section -->
      <div class="header-box" :style="headerBoxStyle" v-if="headerText || headerImg">
        <div class="title-text" v-if="headerType === 0" :style="titleTextStyle">
          {{ headerText }}
        </div>
        <div class="title-img" v-else :style="titleImgBoxStyle">
          <img :src="headerImg" :style="titleImgStyle" alt="" v-if="headerImg" />
          <div class="empty-img" v-else>暂无图片</div>
        </div>
      </div>

      <!-- Single Column -->
      <template v-if="styleConfig == 0">
        <div class="list-wrapper itemA">
          <div
            class="item"
            v-for="(item, index) in list"
            :key="index"
            :style="{
              borderRadius: bgRadius,
            }"
          >
            <div class="img-box">
              <img
                class="img"
                v-if="item.image"
                :src="item.image"
                alt=""
                :style="{
                  borderRadius: imgRadius,
                }"
              />
              <div
                v-else
                class="empty-box"
                :style="{
                  borderRadius: imgRadius,
                }"
              >
                <img src="@diy/assets/images/shan.png" />
              </div>
            </div>
            <div class="info">
              <div class="hd">
                <div
                  class="title line2"
                  v-if="checkboxInfo.indexOf(0) != -1"
                  :style="{
                    fontWeight: goodsName,
                    color: toneConfig ? goodsNameColor : '#333',
                  }"
                >
                  {{ item.store_name || '华为荣耀畅享平板换屏服务 屏幕换外屏主板维修' }}
                </div>
                <img v-if="checkboxInfo.indexOf(1) != -1" src="@diy/assets/images/goods01.png" />
              </div>
              <div
                class="price acea-row row-middle"
                :class="checkboxInfo.indexOf(3) == -1 && checkboxInfo.indexOf(4) == -1 ? 'on' : ''"
              >
                <div
                  class="num"
                  v-if="checkboxInfo.indexOf(2) != -1"
                  :style="{
                    color: toneConfig ? goodsPriceColor : colorStyle.theme,
                  }"
                >
                  <span>￥</span>{{ item.price ? $HandlePrice(item.price, 0) : 33
                  }}<span>{{ item.price ? $HandlePrice(item.price, 1) : '' }}</span>
                </div>
                <img class="img" v-if="checkboxInfo.indexOf(5) != -1" src="@diy/assets/images/goods02.png" />
              </div>
              <div class="bottom">
                <span
                  class="mr8"
                  v-if="checkboxInfo.indexOf(3) != -1"
                  :style="{
                    color: toneConfig ? soldNumColor : '#999999',
                  }"
                  >已售{{ item.sales || 0 }}件</span
                >
                <span
                  v-if="checkboxInfo.indexOf(4) != -1"
                  :style="{
                    color: toneConfig ? scoreColor : '#999999',
                  }"
                  >评分 {{ item.star || 0 }}</span
                >
              </div>
            </div>
            <div v-if="!cartConfig">
              <div
                class="bnt"
                v-if="bntStyleConfig == 0"
                :style="{
                  background: toneCartConfig ? bntBgColor : themeColor,
                }"
              >
                购买
              </div>
              <div
                class="jia"
                v-else
                :style="{
                  background: toneCartConfig ? bntBgColor : themeColor,
                }"
              >
                <div class="jiaCon">
                  <span class="iconfont iconjiahao1" v-if="bntStyleConfig == 1"></span>
                  <span class="iconfont icongouwuche1" v-else></span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </template>

      <!-- Two Columns -->
      <template v-else-if="styleConfig == 1">
        <div class="list-wrapper itemC">
          <div class="item" v-for="(item, index) in list" :key="index">
            <div class="img-box">
              <img
                class="img"
                v-if="item.image"
                :src="item.image"
                alt=""
                :style="{
                  borderRadius: imgRadius2,
                }"
              />
              <div
                v-else
                class="empty-box"
                :style="{
                  borderRadius: imgRadius2,
                }"
              >
                <img src="@diy/assets/images/shan.png" />
              </div>
            </div>
            <div
              class="info"
              :class="
                checkboxInfo.length == 1 && checkboxInfo.indexOf(0) != -1 && !cartConfig
                  ? 'on'
                  : ((checkboxInfo.length == 1 && checkboxInfo.indexOf(4) != -1) || !checkboxInfo.length) && !cartConfig
                  ? 'on2'
                  : ''
              "
              :style="{
                borderRadius: bgRadius2,
              }"
            >
              <div class="hd">
                <div
                  class="title line2"
                  v-if="checkboxInfo.indexOf(0) != -1"
                  :style="{
                    fontWeight: goodsName,
                    color: toneConfig ? goodsNameColor : '#333',
                  }"
                >
                  {{ item.store_name || '蓝牙音乐手表 | Jeep智能表蓝牙通话健康管理 P07' }}
                </div>
                <img v-if="checkboxInfo.indexOf(1) != -1" src="@diy/assets/images/goods01.png" />
              </div>
              <div class="price acea-row row-middle">
                <div
                  class="num mb-10"
                  v-if="checkboxInfo.indexOf(2) != -1"
                  :style="{
                    color: toneConfig ? goodsPriceColor : colorStyle.theme,
                  }"
                >
                  <span>￥</span>{{ item.price ? $HandlePrice(item.price, 0) : 77
                  }}<span>{{ item.price ? $HandlePrice(item.price, 1) : '' }}</span>
                </div>
                <img class="img" v-if="checkboxInfo.indexOf(5) != -1" src="@diy/assets/images/goods02.png" />
              </div>
              <div
                class="bottom"
                v-if="checkboxInfo.indexOf(3) != -1"
                :style="{
                  color: toneConfig ? soldNumColor : '#999999',
                }"
              >
                <span>已售{{ item.sales || 0 }}件</span>
              </div>
            </div>
            <div
              class="jia"
              v-if="!cartConfig"
              :style="{
                background: toneCartConfig ? bntBgColor : themeColor,
              }"
            >
              <div class="jiaCon">
                <span class="iconfont iconjiahao1" v-if="bntStyleConfig == 0"></span>
                <span class="iconfont icongouwuche1" v-else></span>
              </div>
            </div>
          </div>
        </div>
      </template>

      <!-- Three Columns / Sliding -->
      <template v-else>
        <div
          class="list-wrapper itemB"
          :class="styleConfig == 3 ? 'itemD' : ''"
          :style="{
            borderRadius: bgRadius,
          }"
        >
          <div class="list">
            <div class="item" v-for="(item, index) in list" :key="index">
              <div class="img-box">
                <img
                  class="img"
                  v-if="item.image"
                  :src="item.image"
                  alt=""
                  :style="{
                    borderRadius: imgRadius,
                  }"
                />
                <div
                  v-else
                  class="empty-box"
                  :style="{
                    borderRadius: imgRadius,
                  }"
                >
                  <img src="@diy/assets/images/shan.png" />
                </div>
              </div>
              <div
                class="info"
                :class="
                  checkboxInfo.indexOf(2) == -1 && checkboxInfo.indexOf(0) != -1 && !cartConfig
                    ? 'on'
                    : checkboxInfo.indexOf(2) == -1 && checkboxInfo.indexOf(0) == -1 && !cartConfig
                    ? 'on2'
                    : ''
                "
              >
                <div class="hd" v-if="checkboxInfo.indexOf(0) != -1">
                  <div
                    class="title line2"
                    :style="{
                      fontWeight: goodsName,
                      color: toneConfig ? goodsNameColor : '#333',
                    }"
                  >
                    {{ item.store_name || '商品名称商品商名称商品商…' }}
                  </div>
                </div>
                <div class="price" v-if="checkboxInfo.indexOf(2) != -1">
                  <div
                    class="num"
                    :style="{
                      color: toneConfig ? goodsPriceColor : colorStyle.theme,
                    }"
                  >
                    <span>￥</span>{{ item.price ? $HandlePrice(item.price, 0) : 77
                    }}<span>{{ item.price ? $HandlePrice(item.price, 1) : '' }}</span>
                  </div>
                </div>
              </div>
              <div
                class="jia"
                v-if="!cartConfig"
                :style="{
                  background: toneCartConfig ? bntBgColor : themeColor,
                }"
              >
                <div class="jiaCon">
                  <span class="iconfont iconjiahao1" v-if="bntStyleConfig == 0"></span>
                  <span class="iconfont icongouwuche1" v-else></span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </template>
    </div>
  </common_wrapper>
</template>

<script>
// 模块级具名导出：DIY 编辑器新增组件时经聚合层取默认配置工厂
// （原 Vue2 时代通过组件选项 data().defaultConfig 暴露；timestamp 依赖实例 props，故为工厂函数）
export const getDefaultConfig = (num) => ({
  cname: '优品推荐',
  name: 'goodRecommend',
  timestamp: num,
  isHide: false,
  setUp: {
    tabVal: 0,
  },
  // Header Config
  headerTitle: '头部设置',
  headerType: {
    title: '标题类型',
    tabVal: 0,
    tabList: [{ name: '文字' }, { name: '图片' }],
  },
  headerText: {
    title: '标题文字',
    value: '优品推荐',
  },
  headerImg: {
    url: '',
    type: 'code',
    delType: 1,
    name: '上传图片',
  },
  // Content Config
  titleGoods: '商品设置',
  goodsList: {
    max: 20,
    list: [],
  },
  productList: {
    list: [],
  },
  typeConfig: {
    title: '选择方式',
    activeValue: 1,
    list: [
      { activeValue: 1, title: '指定商品' },
      { activeValue: 3, title: '指定分类' },
      { activeValue: 4, title: '商品标签' },
    ],
  },
  goodsSort: {
    title: '商品排序',
    tabVal: 1,
    tabList: [{ name: '综合' }, { name: '销量' }, { name: '价格' }],
  },
  numberConfig: {
    title: '商品数量',
    val: 3,
    min: 1,
  },
  classList: {
    title: '商品分类',
    classVal: [],
  },
  goodsLabel: {
    title: '商品标签',
    activeValue: [],
    list: [],
  },
  checkboxInfo: {
    title: '展示信息',
    name: 'checkboxInfo',
    type: [0, 3, 2, 5], // Name, Price, Member Price (5)
    list: [
      { id: 0, name: '商品名称' },
      { id: 2, name: '商品价格' },
      { id: 5, name: '会员价格' },
      { id: 3, name: '已售数量' },
    ],
  },
  cartConfig: {
    title: '购物车按钮',
    tabVal: 0, // 0: Show, 1: Hide
    tabList: [{ name: '显示' }, { name: '隐藏' }],
  },
  bntStyleConfig: {
    title: '按钮样式',
    tabVal: 0,
    tabList: [
      { name: '样式1', icon: 'icon-circle' },
      { name: '样式2', icon: 'icon-plus' },
      { name: '样式3', icon: 'icon-cart' },
    ],
  },
  bntConfig: {
    title: '按钮效果',
    tabVal: 1,
    tabList: [{ name: '进入商品详情页' }, { name: '加入购物车' }],
  },
  // Style Config
  titleRight: '列表样式', // List Style
  styleConfig: {
    title: '列表样式',
    tabVal: 0,
    tabList: [{ name: '单列展示' }, { name: '两列纵向' }, { name: '三列展示' }, { name: '左右滑动' }],
  },
  headerStyleTitle: '头部样式',
  headerTextConfig: {
    title: '标题文字',
    tabVal: 1, // 0: Bold, 1: Normal, 2: Italic
    tabList: [
      { name: '加粗', style: 'bold' },
      { name: '正常', style: 'normal' },
      { name: '倾斜', style: 'italic' },
    ],
  },
  headerColor: {
    title: '标题颜色',
    default: [{ item: '#333333' }],
    color: [{ item: '#333333' }],
  },
  headerAlign: {
    title: '标题位置',
    tabVal: 1, // 0: Left, 1: Center, 2: Right
    tabList: [
      { name: '左对齐', style: 'left' },
      { name: '居中对齐', style: 'center' },
      { name: '右对齐', style: 'right' },
    ],
  },
  headerFontSize: {
    title: '标题字号',
    val: 16,
    min: 12,
    max: 30,
  },
  cartStyleTitle: '购物车按钮',
  goodsStyleTitle: '商品图样式',
  toneCartConfig: {
    title: '色调',
    tabVal: 0,
    tabList: [{ name: '跟随主题风格' }, { name: '自定义' }],
  },
  bntBgColor: {
    title: '按钮颜色',
    default: [{ item: '#E93323' }, { item: '#FF7931' }],
    color: [{ item: '#E93323' }, { item: '#FF7931' }],
  },
  generalStyleTitle: '通用样式',
  componentBgConfig: {
    title: '背景设置',
    tabVal: 0,
    tabList: [{ name: '颜色' }, { name: '图片' }],
    colorConfig: {
      title: '背景颜色',
      default: [{ item: '#F5F5F5' }],
      color: [{ item: '#F5F5F5' }],
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
  bottomBgColor: {
    title: '底部背景',
    default: [{ item: '#F5F5F5' }],
    color: [{ item: '#F5F5F5' }],
  },
  paddingConfig: {
    title: '内边距',
    isAll: false,
    val: 10,
    min: 0,
    max: 100,
    valList: [{ val: 0 }, { val: 10 }, { val: 0 }, { val: 10 }],
  },
  marginConfig: {
    title: '外边距',
    isAll: false,
    val: 0,
    min: 0,
    max: 100,
    valList: [{ val: 10 }, { val: 0 }, { val: 0 }, { val: 0 }],
  },
  topConfig: {
    title: '上边距',
    val: 0,
    min: 0,
  },
  bottomConfig: {
    title: '下边距',
    val: 0,
    min: 0,
  },
  prConfig: {
    title: '左右边距',
    val: 10,
    min: 0,
  },
  mbConfig: {
    title: '内容间距', // Page Spacing in prompt, using Content Spacing name
    val: 10,
    min: 0,
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
  filletImg: {
    title: '圆角值', // Image Radius
    type: 0,
    val: 8,
    valList: [{ val: 8 }, { val: 8 }, { val: 8 }, { val: 8 }],
  },
  goodsName: {
    title: '商品名称',
    tabVal: 0,
    tabList: [
      { name: '加粗', style: 'bold' },
      { name: '正常', style: 'normal' },
    ],
  },
  toneConfig: {
    title: '色调',
    tabVal: 0,
    tabList: [{ name: '跟随主题风格' }, { name: '自定义' }],
  },
  goodsNameColor: {
    title: '商品名称',
    default: [{ item: '#333333' }],
    color: [{ item: '#333333' }],
  },
  goodsPriceColor: {
    title: '商品价格',
    default: [{ item: '#E93323' }],
    color: [{ item: '#E93323' }],
  },
  soldNumColor: {
    title: '已售数量',
    default: [{ item: '#999999' }],
    color: [{ item: '#999999' }],
  },
  scoreColor: {
    title: '评分',
    default: [{ item: '#999999' }],
    color: [{ item: '#999999' }],
  },
});
</script>

<script setup>
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import { useMobildConfigStore } from '@diy/store/mobildConfig';

defineOptions({
  name: 'home_good_recommend',
  cname: '优品推荐',
  configName: 'c_good_recommend',
  icon: '#iconzujian-youpintuijian',
  // Placeholder icon
  type: 3,
  defaultName: 'goodRecommend',
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

const headerBoxStyle = computed(() => {
  return {
    marginBottom: '10px',
    padding: '0 10px',
    textAlign: headerAlign.value,
  };
});

const titleTextStyle = computed(() => {
  return {
    color: headerColor.value,
    fontSize: headerFontSize.value + 'px',
    fontWeight: headerFontWeight.value,
    fontStyle: headerFontStyle.value,
    textAlign: headerAlign.value,
  };
});

const titleImgBoxStyle = computed(() => {
  return {
    textAlign: headerAlign.value,
  };
});

const titleImgStyle = computed(() => {
  return {
    maxWidth: '100%',
    height: 'auto',
  };
});

const configObj = ref(null);

const list = ref([]);
const pageData = ref({});
const styleConfig = ref(0);
const checkboxInfo = ref([]);
const cartConfig = ref(0);
const bntStyleConfig = ref(0);
const imgRadius = ref(0);
const imgRadius2 = ref(0);
const goodsName = ref('');
const toneConfig = ref(0);
const goodsNameColor = ref('');
const goodsPriceColor = ref('');
const soldNumColor = ref('');
const scoreColor = ref('');
const toneCartConfig = ref(0);
const bntBgColor = ref('');
const bgColor = ref('');
const bottomBgColor = ref('');
const paddingConfig = ref({
  title: '内边距',
  isAll: false,
  val: 10,
  min: 0,
  max: 100,
  valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
});
const marginConfig = ref({
  title: '外边距',
  isAll: false,
  val: 0,
  min: 0,
  max: 100,
  valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
});
const bgRadius = ref(0);
const bgRadius2 = ref(0);
const themeColor = ref('');
// Header Data
const headerType = ref(0);
// 0: Text, 1: Image (In tabList index) -> Wait, tabList[0] is Image, tabList[1] is Text in my config above?
// In default config above: tabList: [{name: '图片'}, {name: '文字'}]. So 0 is Image, 1 is Text.
// Let's stick to this.
const headerText = ref('');
const headerImg = ref('');
const headerFontWeight = ref('normal');
const headerFontStyle = ref('normal');
const headerColor = ref('');
const headerAlign = ref('center');
const headerFontSize = ref(16);
const showHeader = ref(true);

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
  let dataClone = JSON.parse(JSON.stringify(data));
  configObj.value = dataClone;

  paddingConfig.value = dataClone.paddingConfig || {
    title: '内边距',
    val: 10,
    min: 0,
    max: 100,
    isAll: false,
    valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
  };
  marginConfig.value = dataClone.marginConfig || {
    title: '外边距',
    val: 0,
    min: 0,
    max: 100,
    isAll: false,
    valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
  };

  if (!dataClone.paddingConfig) {
    if (dataClone.topConfig) paddingConfig.value.valList[0].val = dataClone.topConfig.val;
    if (dataClone.bottomConfig) paddingConfig.value.valList[2].val = dataClone.bottomConfig.val;
    if (dataClone.prConfig) {
      paddingConfig.value.valList[1].val = dataClone.prConfig.val;
      paddingConfig.value.valList[3].val = dataClone.prConfig.val;
    }
    dataClone.paddingConfig = paddingConfig.value;
  }
  if (!dataClone.marginConfig) {
    if (dataClone.mbConfig) marginConfig.value.valList[0].val = dataClone.mbConfig.val;
    dataClone.marginConfig = marginConfig.value;
  }

  // Ensure new configs exist
  if (!dataClone.zIndexConfig) dataClone.zIndexConfig = defaultConfig.zIndexConfig;
  if (!dataClone.borderConfig) dataClone.borderConfig = defaultConfig.borderConfig;
  if (!dataClone.shadowConfig) dataClone.shadowConfig = defaultConfig.shadowConfig;
  if (!dataClone.componentBgConfig) dataClone.componentBgConfig = defaultConfig.componentBgConfig;

  // Header Config Defaults
  if (!dataClone.headerTitle) dataClone.headerTitle = defaultConfig.headerTitle;
  if (!dataClone.headerType) dataClone.headerType = defaultConfig.headerType;
  if (!dataClone.headerText) dataClone.headerText = defaultConfig.headerText;
  if (!dataClone.headerImg) dataClone.headerImg = defaultConfig.headerImg;
  if (!dataClone.headerStyleTitle) dataClone.headerStyleTitle = defaultConfig.headerStyleTitle;
  if (!dataClone.headerTextConfig) dataClone.headerTextConfig = defaultConfig.headerTextConfig;
  if (!dataClone.headerColor) dataClone.headerColor = defaultConfig.headerColor;
  if (!dataClone.headerAlign) dataClone.headerAlign = defaultConfig.headerAlign;
  if (!dataClone.headerFontSize) dataClone.headerFontSize = defaultConfig.headerFontSize;

  // Header Config Assignment
  headerType.value = dataClone.headerType.tabVal;
  headerText.value = dataClone.headerText.value;
  headerImg.value = dataClone.headerImg.url;

  let headerTextConfig = dataClone.headerTextConfig.tabVal;
  headerFontWeight.value = headerTextConfig == 0 ? 'bold' : 'normal';
  headerFontStyle.value = headerTextConfig == 2 ? 'italic' : 'normal';

  headerColor.value =
    dataClone.headerColor.color && dataClone.headerColor.color[0] ? dataClone.headerColor.color[0].item : '#333';

  const alignList = dataClone.headerAlign.tabList;
  const alignVal = dataClone.headerAlign.tabVal;
  headerAlign.value = alignList && alignList[alignVal] ? alignList[alignVal].style : 'left';

  headerFontSize.value = dataClone.headerFontSize.val;

  if (dataClone.mbConfig) {
    styleConfig.value = dataClone.styleConfig.tabVal;
    checkboxInfo.value = dataClone.checkboxInfo.type;
    cartConfig.value = dataClone.cartConfig.tabVal;
    bntStyleConfig.value = dataClone.bntStyleConfig.tabVal;

    let filletImg = dataClone.filletImg.type;
    let filletValImg = dataClone.filletImg.val;
    let valListImg = dataClone.filletImg.valList;
    imgRadius.value = filletImg
      ? valListImg[0].val + 'px ' + valListImg[1].val + 'px ' + valListImg[3].val + 'px ' + valListImg[2].val + 'px'
      : filletValImg + 'px';
    imgRadius2.value = filletImg
      ? valListImg[0].val + 'px ' + valListImg[1].val + 'px 0 0'
      : filletValImg + 'px ' + filletValImg + 'px 0 0';

    let goodsTabVal = dataClone.goodsName.tabVal;
    goodsName.value = dataClone.goodsName.tabList[goodsTabVal].style;

    toneConfig.value = dataClone.toneConfig.tabVal;
    goodsNameColor.value = dataClone.goodsNameColor.color[0].item;
    goodsPriceColor.value = dataClone.goodsPriceColor.color[0].item;
    soldNumColor.value = dataClone.soldNumColor.color[0].item;
    scoreColor.value = dataClone.scoreColor.color[0].item;

    toneCartConfig.value = dataClone.toneCartConfig.tabVal;
    let bntBgColorLeft = dataClone.bntBgColor.color[0].item;
    let bntBgColorRight = dataClone.bntBgColor.color[1].item;
    bntBgColor.value = `linear-gradient(90deg,${bntBgColorLeft} 0%,${bntBgColorRight} 100%)`;

    bottomBgColor.value = dataClone.bottomBgColor.color[0].item;
    themeColor.value = `linear-gradient(90deg,${props.colorStyle.theme} 0%,${props.colorStyle.gradient} 100%)`;

    let fillet = dataClone.fillet.type;
    let filletVal = dataClone.fillet.val;
    let valList = dataClone.fillet.valList;
    bgRadius.value = fillet
      ? valList[0].val + 'px ' + valList[1].val + 'px ' + valList[3].val + 'px ' + valList[2].val + 'px'
      : filletVal + 'px';
    bgRadius2.value = fillet
      ? '0 0 ' + valList[3].val + 'px ' + valList[2].val + 'px'
      : '0 0 ' + filletVal + 'px ' + filletVal + 'px';

    if (dataClone.typeConfig.activeValue == 1) {
      list.value = dataClone.goodsList.list.length ? dataClone.goodsList.list : 4;
    } else {
      list.value = dataClone.productList.list.length ? dataClone.productList.list : 4;
    }
  }
}
</script>

<style scoped lang="scss">
.mobile-page {
  display: inline-block;
  width: -webkit-fill-available;
}
.itemOn {
  border-radius: 0 !important;
  img,
  .empty-box {
    border-radius: 0 !important;
  }
  .img-box {
    .label {
      border-radius: 0 0 8px 0 !important;
    }
  }
}
.pageOn {
  border-radius: 8px !important;
}
.list-wrapper {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  .item {
    width: 48.5%;
    margin-bottom: 10px;
    background-color: #fff;
    position: relative;
    .bnt {
      width: 48px;
      height: 28px;
      background: linear-gradient(90deg, #e93323 0%, #ff7931 100%);
      border-radius: 25px;
      color: #fff;
      text-align: center;
      line-height: 28px;
      font-size: 12px;
      position: absolute;
      right: 10px;
      bottom: 10px;
    }
    .jia {
      width: 22px;
      height: 22px;
      background-color: #e93323;
      border-radius: 50%;
      position: absolute;
      right: 10px;
      bottom: 10px;
      .jiaCon {
        width: 100%;
        height: 100%;
        text-align: center;
        line-height: 22px;
        .iconfont {
          color: #fff;
          font-size: 13px;
        }
      }
    }
    .img-box {
      position: relative;
      width: 100%;
      height: 173px;
      .img {
        width: 100%;
        height: 100%;
        object-fit: cover;
      }
      img,
      .box {
        width: 65px;
        height: 50px;
      }
      .empty-box {
        background: #f3f9ff;
      }
    }
    .info {
      padding: 7px 10px;
      .title {
        font-size: 14px;
        color: #333;
      }
      img {
        height: 14px;
        display: block;
        margin-top: 4px;
      }
      .bottom {
        color: #999999;
        font-size: 11px;
      }
      .price {
        display: flex;
        align-items: center;
        img {
          width: 70px;
          height: 15px;
          display: block;
        }
        .num {
          font-size: 20px;
          margin-right: 4px;
          font-family: SemiBold;
          span {
            font-size: 12px;
          }
        }
      }
    }
  }
}
.itemA {
  .item {
    display: flex;
    width: 100%;
    padding: 10px;
    position: relative;
    .img-box {
      position: relative;
      width: 112px;
      height: 112px;
    }
    .info {
      display: flex;
      justify-content: space-between;
      flex-direction: column;
      flex: 1;
      margin-left: 10px;
      padding: 0;
      .hd {
        height: 63px;
      }
      .price {
        margin-top: 2px;
        &.on {
          margin-top: 20px;
        }
      }
    }
  }
}
.itemB {
  justify-content: inherit;
  background-color: #fff;
  padding: 16px 10px 0 10px;
  width: 100%;
  box-sizing: border-box;
  .list {
    display: flex;
    flex-wrap: wrap;
    width: 100%;
    column-gap: 10px;
  }
  .item {
    width: calc((100% - 20px) / 3);
    margin-right: 0;
    background: unset;
    .jia {
      right: 2px;
      bottom: 0;
    }
    .info {
      padding: 0;
      &.on {
        height: 70px;
      }
      &.on2 {
        height: 30px;
      }
      .hd {
        margin-top: 7px;
        height: 42px;
      }
      .price {
        margin-top: 7px;
        line-height: 1.2;
      }
    }
    .img-box {
      position: relative;
      width: 100%;
      height: 110px;
      img,
      .box,
      .empty-box {
        border-radius: 10px 10px 0 0;
      }
    }
  }
}
.itemD {
  flex-wrap: nowrap;
  display: flex;
  overflow-x: auto;
  padding-bottom: 10px; // Space for scrollbar or visual
  &::-webkit-scrollbar {
    display: none;
  }
  .list {
    flex-wrap: nowrap;
    // justify-content: center; // Don't center, let it flow
    // align-items: center;
  }
  .item {
    width: 110px; // Fixed width for sliding items
    flex-shrink: 0;
    margin-right: 10px;
    background: unset;
    // &:nth-child(3n) {
    //   margin-right: 10px; // Reset 3n margin removal for sliding
    // }
    &:last-child {
      margin-right: 0;
    }
    .img-box {
      height: 110px;
    }
  }
}
.itemC {
  .item {
    background-color: transparent;
    .info {
      background-color: #fff;
    }
  }
  .item .info.on {
    height: 67px;
  }
  .item .info.on2 {
    height: 40px;
  }
  .item .info .price {
    margin-top: 6px;
    margin-bottom: 8px;
  }
  .item .info .bottom {
    margin-top: 3px;
  }
}
.title-img {
  display: inline-block;
}
</style>
