<template>
  <common_wrapper :config="configObj">
    <div
      class="pointsMall"
      :style="{
        borderRadius: bgRadius,
        overflow: 'hidden',
      }"
    >
      <div
        class="title acea-row row-between-wrapper"
        :style="
          styleConfig
            ? 'backgroundImage:url(' + imgBgUrl + ')'
            : `background:linear-gradient(90deg,${headerBgColorLeft} 0%,${headerBgColorRight} 100%)`
        "
      >
        <div
          v-if="titleConfig"
          :style="
            (titleTabVal == 2 ? 'fontStyle:' : 'fontWeight:') +
            titleText +
            ';color:' +
            titleColor +
            ';fontSize:' +
            titleNumber +
            'px;'
          "
        >
          {{ titleTxtConfig }}
        </div>
        <img v-else :src="styleConfig ? imgUrl : imgColorUrl" alt="" />
        <div
          class="more"
          :style="{
            color: styleConfig ? headerBntColor : headerBntColor2,
            fontSize: bntNumber + 'px',
          }"
        >
          {{ rightBntTxt
          }}<span
            class="iconfont iconjinru"
            :style="{
              fontSize: bntNumber + 'px',
            }"
          ></span>
        </div>
      </div>
      <div
        class="conter"
        v-if="goodStyleConfig == 0"
        :style="{
          background: styleConfig ? bgColor : bgColor2,
          borderRadius: bgRadius2,
        }"
      >
        <div class="list">
          <div class="item" v-for="(item, index) in numberConfig" :key="index">
            <div
              class="pictrue acea-row row-center-wrapper"
              :style="{
                borderRadius: imgRadius,
              }"
            >
              <img src="@diy/assets/images/shan.png" />
            </div>
            <div
              class="bottom"
              :style="{
                color: toneConfig ? goodsPriceColor : '#fff',
                background: toneConfig
                  ? `linear-gradient(90deg,${priceBgColorRight} 0%,${priceBgColorLeft} 100%)`
                  : themeColor,
              }"
            >
              68880积分
            </div>
          </div>
        </div>
      </div>
      <div
        class="list on"
        v-else-if="goodStyleConfig == 1"
        :style="{
          background: styleConfig ? bgColor : bgColor2,
          borderRadius: bgRadius2,
        }"
      >
        <div class="item" v-for="(item, index) in numberConfig" :key="index">
          <div
            class="pictrue acea-row row-center-wrapper"
            :style="{
              borderRadius: imgRadius,
            }"
          >
            <img src="@diy/assets/images/shan.png" />
          </div>
          <div class="money acea-row row-middle">
            <img src="@diy/assets/images/points.png" /><span
              class="num"
              :style="{
                color: !toneConfig
                  ? styleConfig
                    ? '#fff'
                    : colorStyle.theme
                  : styleConfig
                  ? goodsPriceColor
                  : goodsPriceColor2,
              }"
              >6888</span
            >
          </div>
          <div
            class="name"
            :style="{
              color: styleConfig ? goodsNameColor2 : goodsNameColor,
            }"
          >
            小米蓝牙耳机...
          </div>
        </div>
      </div>
      <div
        class="list on2"
        v-else
        :style="{
          background: styleConfig ? bgColor : bgColor2,
          borderRadius: bgRadius2,
        }"
      >
        <div class="item" v-for="(item, index) in numberConfig" :key="index">
          <div
            class="pictrue acea-row row-center-wrapper"
            :style="{
              borderRadius: imgRadius,
            }"
          >
            <img src="@diy/assets/images/shan.png" />
          </div>
          <div
            class="name"
            :style="{
              color: styleConfig ? goodsNameColor2 : goodsNameColor,
            }"
          >
            小米蓝牙耳机你值得拥有
          </div>
          <div class="money acea-row row-middle">
            <img src="@diy/assets/images/points.png" /><span
              class="num on"
              :style="{
                color: !toneConfig
                  ? styleConfig
                    ? '#fff'
                    : colorStyle.theme
                  : styleConfig
                  ? goodsPriceColor
                  : goodsPriceColor2,
              }"
              >6888</span
            >
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
  cname: '积分商城',
  name: 'pointsMall',
  timestamp: num,
  isHide: false,
  setUp: {
    tabVal: 0,
  },
  titleLeft: '头部设置',
  titleGoodsList: '商品列表',
  titleGoods: '商品设置',
  titleRight: '头部样式',
  titleGoodsStyle: '商品样式',
  titleCurrency: '通用样式',
  styleConfig: {
    title: '选择风格',
    tabVal: 1,
    tabList: [
      {
        name: '背景色',
      },
      {
        name: '背景图片',
      },
    ],
  },
  titleConfig: {
    title: '标题类型',
    tabVal: 0,
    tabList: [
      {
        name: '图片',
      },
      {
        name: '文字',
      },
    ],
  },
  imgBgConfig: {
    info: '建议：710px * 96px',
    url: Setting.apiBaseURL.replace(/adminapi/, '') + 'statics/images/pointsBg.png',
    type: 'code',
    delType: 1,
    name: '背景图片',
  },
  imgConfig: {
    info: '建议：154px * 32px',
    url: __asset_points01_0,
    type: 'code',
    delType: 1,
    name: '标题图片',
  },
  imgConfig2: {
    info: '建议：154px * 32px',
    url: __asset_points02_1,
    type: 'code',
    delType: 1,
    name: '标题图片',
  },
  titleTxtConfig: {
    title: '标题文字',
    value: '积分兑好礼',
    place: '请输入标题文字',
    max: 10,
  },
  rightBntConfig: {
    title: '右侧按钮',
    value: '更多',
    place: '请输入右侧按钮',
    max: 6,
  },
  numberConfig: {
    title: '商品数量',
    val: 3,
    min: 1,
  },
  goodStyleConfig: {
    title: '选择风格',
    tabVal: 0,
    tabList: [
      {
        name: '样式1',
      },
      {
        name: '样式2',
      },
      {
        name: '样式3',
      },
    ],
  },
  headerBgColor: {
    title: '背景颜色',
    name: 'headerBgColor',
    default: [
      {
        item: '#fff',
      },
      {
        item: '#fff',
      },
    ],
    color: [
      {
        item: '#fff',
      },
      {
        item: '#fff',
      },
    ],
  },
  titleText: {
    title: '标题文字',
    tabVal: 0,
    tabList: [
      {
        name: '加粗',
        style: 'bold',
      },
      {
        name: '正常',
        style: 'normal',
      },
      {
        name: '倾斜',
        style: 'italic',
      },
    ],
  },
  titleColor: {
    title: '标题颜色',
    name: 'titleColor',
    default: [
      {
        item: '#333333',
      },
    ],
    color: [
      {
        item: '#333333',
      },
    ],
  },
  titleNumber: {
    title: '标题字号',
    val: 16,
    min: 0,
  },
  headerBntColor: {
    title: '按钮颜色',
    name: 'headerBntColor',
    default: [
      {
        item: '#fff',
      },
    ],
    color: [
      {
        item: '#fff',
      },
    ],
  },
  headerBntColor2: {
    title: '按钮颜色',
    name: 'headerBntColor2',
    default: [
      {
        item: '#999',
      },
    ],
    color: [
      {
        item: '#999',
      },
    ],
  },
  bntNumber: {
    title: '按钮字号',
    val: 12,
    min: 0,
  },
  filletImg: {
    title: '商品圆角',
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
    val: 5,
    min: 0,
    valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
  },
  goodsNameColor: {
    title: '商品名称',
    name: 'goodsNameColor',
    default: [
      {
        item: '#282828',
      },
    ],
    color: [
      {
        item: '#282828',
      },
    ],
  },
  goodsNameColor2: {
    title: '商品名称',
    name: 'goodsNameColor2',
    default: [
      {
        item: '#fff',
      },
    ],
    color: [
      {
        item: '#fff',
      },
    ],
  },
  goodsUnitPriceColor2: {
    title: '价格单位',
    name: 'goodsUnitPriceColor2',
    default: [
      {
        item: '#282828',
      },
    ],
    color: [
      {
        item: '#282828',
      },
    ],
  },
  goodsUnitPriceColor: {
    title: '价格单位',
    name: 'goodsUnitPriceColor',
    default: [
      {
        item: '#fff',
      },
    ],
    color: [
      {
        item: '#fff',
      },
    ],
  },
  toneConfig: {
    title: '色调',
    tabVal: 0,
    tabList: [
      {
        name: '跟随主题风格',
      },
      {
        name: '自定义',
      },
    ],
  },
  goodsPriceColor: {
    title: '商品价格',
    name: 'goodsPriceColor',
    default: [
      {
        item: '#fff',
      },
    ],
    color: [
      {
        item: '#fff',
      },
    ],
  },
  goodsPriceColor2: {
    title: '商品价格',
    name: 'goodsPriceColor2',
    default: [
      {
        item: '#E93323',
      },
    ],
    color: [
      {
        item: '#E93323',
      },
    ],
  },
  priceBgColor: {
    title: '价格背景',
    name: 'priceBgColor',
    default: [
      {
        item: '#FF7931',
      },
      {
        item: '#E93323',
      },
    ],
    color: [
      {
        item: '#FF7931',
      },
      {
        item: '#E93323',
      },
    ],
  },
  moduleColor: {
    title: '组件背景',
    name: 'moduleColor',
    default: [
      {
        item: '#FF7931',
      },
      {
        item: '#E93323',
      },
    ],
    color: [
      {
        item: '#FF7931',
      },
      {
        item: '#E93323',
      },
    ],
  },
  moduleColor2: {
    title: '组件背景',
    name: 'moduleColor',
    default: [
      {
        item: '#fff',
      },
      {
        item: '#fff',
      },
    ],
    color: [
      {
        item: '#fff',
      },
      {
        item: '#fff',
      },
    ],
  },
  bottomBgColor: {
    title: '底部背景',
    name: 'bgColor',
    default: [
      {
        item: '#f5f5f5',
      },
    ],
    color: [
      {
        item: '#f5f5f5',
      },
    ],
  },
  componentBgConfig: {
    title: '组件背景',
    tabVal: 0,
    tabList: [{ name: '颜色' }, { name: '图片' }],
    colorConfig: {
      title: '背景颜色',
      default: [{ item: '#f5f5f5' }, { item: '#f5f5f5' }],
      color: [{ item: '#f5f5f5' }, { item: '#f5f5f5' }],
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
    title: '页面上间距',
    val: 0,
    min: 0,
  },
  zIndexConfig: {
    title: '组件上浮',
    val: 0,
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
});
</script>

<script setup>
import { ref, watch, onMounted, nextTick } from 'vue';
import { useMobildConfigStore } from '@diy/store/mobildConfig';
import __asset_points01_0 from '@diy/assets/images/points01.png';
import __asset_points02_1 from '@diy/assets/images/points02.png';
import Setting from '@/setting';

defineOptions({
  name: 'points_mall',
  cname: '积分商城',
  configName: 'c_points_mall',
  icon: '#iconzujian-jifenshangcheng',
  type: 1,
  // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'pointsMall',
  // 插件归属组件：经 diy/component_status 白名单放行（配合清单 diy.components 声明）
  pluginComponent: true,
});

const props = defineProps({
  index: {
    type: null,
    default: -1,
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

// 默认初始化数据禁止修改
const configObj = ref(null);

const pageData = ref({});
const bottomBgColor = ref('');
const paddingConfig = ref({
  title: '内边距',
  val: 0,
  min: 0,
  max: 100,
  isAll: false,
  valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
});
const marginConfig = ref({
  title: '外边距',
  val: 0,
  min: 0,
  max: 100,
  isAll: false,
  valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
});
const topConfig = ref(0);
const bottomConfig = ref(0);
const prConfig = ref(0);
const styleConfig = ref(0);
const imgBgUrl = ref(0);
const headerBgColorLeft = ref('');
const headerBgColorRight = ref('');
const titleConfig = ref(0);
const imgUrl = ref('');
const imgColorUrl = ref('');
const headerBntColor = ref('');
const headerBntColor2 = ref('');
const titleTabVal = ref(0);
const titleText = ref('');
const titleColor = ref('');
const titleNumber = ref(0);
const titleTxtConfig = ref('');
const rightBntTxt = ref('');
const numberConfig = ref(0);
const goodStyleConfig = ref(0);
const bntNumber = ref(0);
const imgRadius = ref(0);
const toneConfig = ref(0);
const goodsPriceColor = ref('');
const goodsPriceColor2 = ref('');
const priceBgColorLeft = ref('');
const priceBgColorRight = ref('');
const bgColor = ref('');
const bgColor2 = ref('');
const mTop = ref(0);
const bgRadius = ref(0);
const bgRadius2 = ref(0);
const goodsNameColor = ref('');
const goodsNameColor2 = ref('');
const goodsUnitPriceColor = ref('');
const goodsUnitPriceColor2 = ref('');
const themeColor = ref('');
const zIndexConfig = ref(0);

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
  for (let key in defaultConfig) {
    if (dataClone[key] == undefined) {
      dataClone[key] = JSON.parse(JSON.stringify(defaultConfig[key]));
    }
  }

  if (!data.componentBgConfig && data.bottomBgColor) {
    dataClone.componentBgConfig.colorConfig.color[0].item = data.bottomBgColor.color[0].item;
    dataClone.componentBgConfig.colorConfig.color[1].item = data.bottomBgColor.color[0].item;
  }

  if (!data.paddingConfig) {
    if (dataClone.topConfig) dataClone.paddingConfig.valList[0].val = dataClone.topConfig.val;
    if (dataClone.bottomConfig) dataClone.paddingConfig.valList[2].val = dataClone.bottomConfig.val;
    if (dataClone.prConfig) {
      dataClone.paddingConfig.valList[1].val = dataClone.prConfig.val;
      dataClone.paddingConfig.valList[3].val = dataClone.prConfig.val;
    }
  }
  if (!data.marginConfig) {
    if (dataClone.mbConfig) dataClone.marginConfig.valList[0].val = dataClone.mbConfig.val;
  }
  paddingConfig.value = dataClone.paddingConfig;
  marginConfig.value = dataClone.marginConfig;
  zIndexConfig.value = dataClone.zIndexConfig.val;
  configObj.value = dataClone;

  styleConfig.value = dataClone.styleConfig.tabVal;
  imgBgUrl.value = dataClone.imgBgConfig.url;
  headerBgColorLeft.value = dataClone.headerBgColor.color[0].item;
  headerBgColorRight.value = dataClone.headerBgColor.color[1].item;
  titleConfig.value = dataClone.titleConfig.tabVal;
  imgUrl.value = dataClone.imgConfig.url;
  imgColorUrl.value = dataClone.imgConfig2.url;
  headerBntColor.value = dataClone.headerBntColor.color[0].item;
  headerBntColor2.value = dataClone.headerBntColor2.color[0].item;
  bntNumber.value = dataClone.bntNumber.val;
  let tabVal = dataClone.titleText.tabVal;
  titleTabVal.value = tabVal;
  titleText.value = dataClone.titleText.tabList[tabVal].style;
  titleColor.value = dataClone.titleColor.color[0].item;
  titleNumber.value = dataClone.titleNumber.val;
  titleTxtConfig.value = dataClone.titleTxtConfig.value;
  rightBntTxt.value = dataClone.rightBntConfig.value;
  numberConfig.value = dataClone.numberConfig.val;
  goodStyleConfig.value = dataClone.goodStyleConfig.tabVal;
  let filletImg = dataClone.filletImg.type;
  let filletValImg = dataClone.filletImg.val;
  let valListImg = dataClone.filletImg.valList;
  imgRadius.value = filletImg
    ? valListImg[0].val + 'px ' + valListImg[1].val + 'px ' + valListImg[3].val + 'px ' + valListImg[2].val + 'px'
    : filletValImg + 'px';
  toneConfig.value = dataClone.toneConfig.tabVal;
  goodsPriceColor.value = dataClone.goodsPriceColor.color[0].item;
  goodsPriceColor2.value = dataClone.goodsPriceColor2.color[0].item;
  priceBgColorLeft.value = dataClone.priceBgColor.color[0].item;
  priceBgColorRight.value = dataClone.priceBgColor.color[1].item;
  let bgColorLeft = dataClone.moduleColor.color[0].item;
  let bgColorRight = dataClone.moduleColor.color[1].item;
  bgColor.value = `linear-gradient(90deg,${bgColorRight} 0%,${bgColorLeft} 100%)`;
  let bgColorLeft2 = dataClone.moduleColor2.color[0].item;
  let bgColorRight2 = dataClone.moduleColor2.color[1].item;
  bgColor2.value = `linear-gradient(90deg,${bgColorRight2} 0%,${bgColorLeft2} 100%)`;
  bottomBgColor.value = dataClone.bottomBgColor.color[0].item;
  let fillet = dataClone.fillet.type;
  let filletVal = dataClone.fillet.val;
  let valList = dataClone.fillet.valList;
  bgRadius.value = fillet
    ? valList[0].val + 'px ' + valList[1].val + 'px 0 0'
    : filletVal + 'px ' + filletVal + 'px 0 0';
  bgRadius2.value = fillet
    ? '0 0 ' + valList[3].val + 'px ' + valList[2].val + 'px'
    : '0 0 ' + filletVal + 'px ' + filletVal + 'px';
  goodsNameColor.value = dataClone.goodsNameColor.color[0].item;
  goodsNameColor2.value = dataClone.goodsNameColor2.color[0].item;
  goodsUnitPriceColor.value = dataClone.goodsUnitPriceColor.color[0].item;
  goodsUnitPriceColor2.value = dataClone.goodsUnitPriceColor2.color[0].item;
  themeColor.value = `linear-gradient(90deg,${props.colorStyle.theme} 0%,${props.colorStyle.gradient} 100%)`;
}
</script>

<style scoped lang="scss">
.pointsMall {
  .title {
    font-size: 16px;
    color: #333;
    background-repeat: no-repeat;
    background-size: 100% 100%;
    width: 100%;
    height: 48px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 16px;
    font-weight: 500;
    padding: 0 12px;
    img {
      width: 88px;
      height: 16px;
      display: block;
    }
    .more {
      font-size: 12px;
      color: #999;
      .iconfont {
        font-size: 12px;
      }
    }
  }
  .conter {
    padding: 0 0 16px 10px;
    background: linear-gradient(90deg, #e93323 0%, #ff7931 100%);
  }
  .list {
    background-color: #fff;
    padding: 10px 0 10px 10px;
    border-radius: 8px 0 0 8px;
    display: flex;
    overflow: hidden;
    &.on2 {
      flex-wrap: wrap;
      padding-top: 0;
      .item {
        width: 47.4%;
        margin-right: 11px;
        margin-bottom: 10px;
        &:nth-of-type(2n) {
          margin-right: 0;
        }
        &:nth-last-child(1),
        &:nth-last-child(2) {
          margin-bottom: 0;
        }
        .pictrue {
          width: 100%;
          height: 162px;
        }
        .name {
          font-size: 14px;
          margin-top: 5px;
        }
        .money {
          margin-top: 0;
          .num {
            &.on {
              font-size: 16px;
              font-family: D-DIN-PRO, D-DIN-PRO;
              font-weight: 600;
            }
          }
        }
      }
    }
    &.on {
      flex-wrap: wrap;
      padding-top: 0;
      .item {
        width: 30.7%;
        margin-right: 9px;
        margin-bottom: 10px;
        &:nth-last-child(1),
        &:nth-last-child(2),
        &:nth-last-child(3) {
          margin-bottom: 0;
        }
        .pictrue {
          width: 100%;
          height: 106px;
        }
      }
    }
    .item {
      width: 112px;
      margin-right: 10px;
      .pictrue {
        width: 112px;
        height: 112px;
        background-color: #f3f9ff;
        img {
          width: 65px;
          height: 50px;
        }
      }
      .bottom {
        width: 98px;
        height: 18px;
        background: linear-gradient(90deg, #e93323 0%, #ff7931 100%);
        border-radius: 1px 10px 10px 10px;
        text-align: center;
        line-height: 18px;
        color: #fff;
        font-size: 11px;
        margin-top: 8px;
      }
      .money {
        font-size: 12px;
        color: #666;
        margin-top: 8px;
        img {
          width: 16px;
          height: 16px;
          display: block;
          margin-right: 4px;
        }
        .num {
          color: #e93323;
        }
      }
      .name {
        color: #282828;
        font-size: 13px;
        margin-top: 3px;
      }
    }
  }
}
</style>
