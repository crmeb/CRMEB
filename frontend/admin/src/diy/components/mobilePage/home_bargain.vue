<template>
  <common_wrapper v-if="configObj" :config="configObj">
    <div class="seckill-box">
      <div
        class="hd"
        :style="
          styleConfig
            ? 'backgroundImage:url(' + imgBgUrl + ')'
            : `background:linear-gradient(90deg,${headerBgColorLeft} 0%,${headerBgColorRight} 100%)`
        "
      >
        <div class="left acea-row row-middle">
          <div
            class="text"
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
            class="line"
            :style="{
              background: dividerColor,
            }"
          ></div>
          <div
            class="tips"
            :style="{
              color: styleConfig ? tipsColor : tipsColor2,
            }"
          >
            {{ tipTxt }}
          </div>
        </div>
        <div
          class="right"
          :style="{
            color: styleConfig ? headerBntColor : headerBntColor2,
            fontSize: bntNumber + 'px',
          }"
        >
          {{ rightBntTxt }}
          <span
            class="iconfont iconjinru"
            :style="{
              fontSize: bntNumber + 'px',
            }"
          ></span>
        </div>
      </div>
      <div
        class="list-wrapper"
        :class="
          goodStyleConfig == 0
            ? 'on'
            : goodStyleConfig == 1 || goodStyleConfig == 2
            ? 'on2'
            : goodStyleConfig == 3
            ? 'on3'
            : ''
        "
        :style="{
          background: bgColor,
        }"
      >
        <template v-if="goodStyleConfig == 0">
          <div class="itemOne acea-row" v-for="(item, index) in numberConfig" :key="index">
            <div
              class="empty-box"
              :style="{
                borderRadius: imgRadius,
              }"
            >
              <img src="@diy/assets/images/shan.png" />
            </div>
            <div class="text">
              <div class="top">
                <div
                  class="name line2"
                  v-if="checkboxInfo.indexOf(0) != -1"
                  :style="{
                    fontWeight: goodsName,
                    color: goodsNameColor,
                  }"
                >
                  熙米家藏青色工装锥形裤 les 中性风帅T无性别中性多口...
                </div>
                <div
                  class="num"
                  v-if="checkboxInfo.indexOf(1) != -1"
                  :style="{
                    color: toneConfig ? joinNumColor : colorStyle.theme,
                  }"
                >
                  <span class="iconfont iconic_fire"></span>1223人正在参与
                </div>
              </div>
              <div
                class="bottom"
                :class="checkboxInfo.indexOf(2) != -1 && checkboxInfo.indexOf(3) != -1 ? '' : 'acea-row row-bottom'"
              >
                <div
                  class="price"
                  v-if="checkboxInfo.indexOf(2) != -1"
                  :style="{
                    color: toneConfig ? bargainPriceColor : colorStyle.theme,
                  }"
                >
                  <span class="label">¥</span><span class="num">2690.00</span>
                </div>
                <div
                  class="yprice"
                  v-if="checkboxInfo.indexOf(3) != -1"
                  :style="{
                    color: goodsPriceColor,
                  }"
                >
                  ¥1233423.00
                </div>
              </div>
              <div
                class="bnt"
                v-if="!bargainConfig"
                :style="{
                  color: toneConfig ? goodsBntTxtColor : '#fff',
                  background: toneConfig
                    ? `linear-gradient(270deg,${goodsBntColorRight} 0%,${goodsBntColorLeft} 100%)`
                    : themeColor,
                }"
              >
                参与砍价
              </div>
            </div>
          </div>
        </template>
        <template v-if="goodStyleConfig == 1">
          <div class="itemTwo" v-for="(item2, index2) in numberConfig" :key="index2">
            <div
              class="empty-box"
              :style="{
                borderRadius: imgRadius,
              }"
            >
              <img src="@diy/assets/images/shan.png" />
            </div>
            <div
              :class="
                (checkboxInfo.indexOf(0) != -1 && checkboxInfo.length == 1 && !bargainConfig) ||
                (checkboxInfo.indexOf(0) != -1 &&
                  checkboxInfo.indexOf(1) != -1 &&
                  checkboxInfo.length == 2 &&
                  !bargainConfig)
                  ? 'item'
                  : (!checkboxInfo.length || (checkboxInfo.indexOf(1) != -1 && checkboxInfo.length == 1)) &&
                    !bargainConfig
                  ? 'item2'
                  : ''
              "
            >
              <div
                class="title line1"
                v-if="checkboxInfo.indexOf(0) != -1"
                :style="{
                  fontWeight: goodsName,
                  color: goodsNameColor,
                }"
              >
                熙米家藏青色工装锥形裤 les 中性风帅T无性别中性多口...
              </div>
              <div
                class="price"
                :class="checkboxInfo.indexOf(3) == -1 && !bargainConfig ? 'on' : ''"
                v-if="checkboxInfo.indexOf(2) != -1"
                :style="{
                  color: toneConfig ? bargainPriceColor : colorStyle.theme,
                }"
              >
                ¥<span class="num">3200.00</span>
              </div>
              <div
                class="yprice"
                :class="checkboxInfo.indexOf(2) == -1 && !bargainConfig ? 'on' : ''"
                v-if="checkboxInfo.indexOf(3) != -1"
                :style="{
                  color: goodsPriceColor,
                }"
              >
                ¥3699.00
              </div>
              <div
                class="bnt"
                :class="checkboxInfo.indexOf(2) == -1 && !bargainConfig ? 'on' : ''"
                v-if="!bargainConfig"
                :style="{
                  color: toneConfig ? goodsBntTxtColor : '#fff',
                  background: toneConfig
                    ? `linear-gradient(90deg,${goodsBntColorRight} 0%,${goodsBntColorLeft} 100%)`
                    : themeColor,
                }"
              >
                去砍价
              </div>
            </div>
          </div>
        </template>
        <template v-if="goodStyleConfig == 2">
          <div class="list-item" v-for="(item, index) in numberConfig" :key="index">
            <div class="img-box">
              <div
                class="empty-box"
                :style="{
                  borderRadius: imgRadius,
                }"
              >
                <img src="@diy/assets/images/shan.png" />
              </div>
            </div>
            <div
              class="title line1"
              v-if="checkboxInfo.indexOf(0) != -1"
              :style="{
                fontWeight: goodsName,
                color: goodsNameColor,
              }"
            >
              熙米家藏青色工装锥形裤 les 中性风帅T无性别中性多口...
            </div>
            <div
              class="price"
              v-if="checkboxInfo.indexOf(2) != -1"
              :style="{
                color: toneConfig ? bargainPriceColor : colorStyle.theme,
              }"
            >
              低至<span class="lable">¥</span><span class="num">350.00</span>
            </div>
            <div
              class="yprice"
              v-if="checkboxInfo.indexOf(3) != -1"
              :style="{
                color: goodsPriceColor,
              }"
            >
              ¥3699.00
            </div>
          </div>
        </template>
        <template v-if="goodStyleConfig == 3">
          <div class="itemThree" v-for="(item2, index2) in numberConfig" :key="index2">
            <div
              class="empty-box"
              :style="{
                borderRadius: imgRadius,
              }"
            >
              <img src="@diy/assets/images/shan.png" />
            </div>
            <div>
              <div
                class="title line1"
                v-if="checkboxInfo.indexOf(0) != -1"
                :style="{
                  fontWeight: goodsName,
                  color: goodsNameColor,
                }"
              >
                熙米家藏青色工装锥形裤 les 中性风帅T无性别中性多口...
              </div>
              <div
                class="joinNum"
                v-if="checkboxInfo.indexOf(1) != -1"
                :style="{
                  color: toneConfig ? joinNumColor2 : '#fff',
                  background: toneConfig
                    ? `linear-gradient(90deg,${joinBgColorLeft} 0%,${joinBgColorRight} 100%)`
                    : themeColor2,
                }"
              >
                175人参与活动
              </div>
              <div
                class="price"
                :class="checkboxInfo.indexOf(3) == -1 && !bargainConfig ? 'on' : ''"
                v-if="checkboxInfo.indexOf(2) != -1"
                :style="{
                  color: toneConfig ? bargainPriceColor : colorStyle.theme,
                }"
              >
                ¥<span class="num">3200.00</span>
              </div>
              <div
                class="yprice"
                :class="checkboxInfo.indexOf(2) == -1 && !bargainConfig ? 'on' : ''"
                v-if="checkboxInfo.indexOf(3) != -1"
                :style="{
                  color: goodsPriceColor,
                }"
              >
                ¥3699.00
              </div>
            </div>
          </div>
        </template>
      </div>
    </div>
  </common_wrapper>
</template>

<script>
// 模块级具名导出：DIY 编辑器新增组件时经聚合层取默认配置工厂
// （原 Vue2 时代通过组件选项 data().defaultConfig 暴露；timestamp 依赖实例 props，故为工厂函数）
export const getDefaultConfig = (num) => ({
  cname: '砍价',
  name: 'bargain',
  desc: '砍价的介绍',
  timestamp: num,
  isHide: false,
  setUp: {
    tabVal: 0,
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
  },
  shadowConfig: {
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
  headerBgColor: {
    title: '头部背景',
    name: 'headerBgColor',
    default: [
      {
        item: '#F62C2C',
      },
      {
        item: '#F96E29',
      },
    ],
    color: [
      {
        item: '#F62C2C',
      },
      {
        item: '#F96E29',
      },
    ],
  },
  imgBgConfig: {
    info: '建议：710px * 96px',
    url: Setting.apiBaseURL.replace(/adminapi/, '') + 'statics/images/bargainBg.png',
    type: 'code',
    delType: 1,
    name: '背景图片',
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
  imgConfig: {
    info: '建议：154px * 32px',
    url: __asset_bargain02_0,
    type: 'code',
    delType: 1,
    name: '标题图片',
  },
  imgColorConfig: {
    info: '建议：154px * 32px',
    url: __asset_bargain01_1,
    type: 'code',
    delType: 1,
    name: '标题图片',
  },
  titleTxtConfig: {
    title: '标题文字',
    value: '疯狂砍价',
    place: '请输入标题文字',
    max: 6,
  },
  tipTxtConfig: {
    title: '提示文字',
    value: '低至0元免费拿',
    place: '请输入提示文字',
    max: 10,
  },
  rightBntConfig: {
    title: '右侧按钮',
    value: '更多',
    place: '请输入右侧按钮',
    max: 4,
  },
  goodStyleConfig: {
    title: '选择风格',
    tabVal: 0,
    tabList: [
      {
        name: '单列展示',
      },
      {
        name: '两列展示',
      },
      {
        name: '三列展示',
      },
      {
        name: '左右滑动',
      },
    ],
  },
  numberConfig: {
    title: '商品数量',
    val: 3,
    min: 1,
  },
  checkboxInfo: {
    title: '展示信息',
    name: 'checkboxInfo',
    type: [0, 1, 2, 3],
    list: [
      {
        id: 0,
        name: '商品名称',
      },
      {
        id: 1,
        name: '参与人数',
      },
      {
        id: 2,
        name: '商品价格',
      },
      {
        id: 3,
        name: '划线价',
      },
    ],
  },
  bargainConfig: {
    title: '砍价按钮',
    tabVal: 0,
    tabList: [
      {
        name: '显示',
      },
      {
        name: '隐藏',
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
        item: '#282828',
      },
    ],
    color: [
      {
        item: '#282828',
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
  tipsColor: {
    title: '提示文字',
    name: 'tipsColor',
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
  tipsColor2: {
    title: '提示文字',
    name: 'tipsColor2',
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
  moduleColor: {
    title: '组件背景',
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
  dividerColor: {
    title: '分割线',
    name: 'dividerColor',
    default: [
      {
        item: '#DDDDDD',
      },
    ],
    color: [
      {
        item: '#DDDDDD',
      },
    ],
  },
  filletImg: {
    title: '图片圆角',
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
  },
  goodsName: {
    title: '商品名称',
    tabVal: 1,
    tabList: [
      {
        name: '加粗',
        style: 'bold',
      },
      {
        name: '正常',
        style: 'normal',
      },
    ],
  },
  goodsNameColor: {
    title: '商品名称',
    name: 'goodsNameColor',
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
  goodsPriceColor: {
    title: '划线价',
    name: 'goodsPriceColor',
    default: [
      {
        item: '#999999',
      },
    ],
    color: [
      {
        item: '#999999',
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
  joinNumColor: {
    title: '参与人数',
    name: 'joinNumColor',
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
  joinNumColor2: {
    title: '参与人数',
    name: 'joinNumColor2',
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
  joinBgColor: {
    title: '参与背景',
    name: 'progressColor',
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
  bargainPriceColor: {
    title: '砍价价格',
    name: 'bargainPriceColor',
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
  goodsBntColor: {
    title: '按钮颜色',
    name: 'goodsBntColor',
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
  goodsBntTxtColor: {
    title: '按钮文字',
    name: 'goodsBntTxtColor',
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
  componentBgConfig: {
    title: '背景设置',
    tabVal: 0,
    tabList: [{ name: '颜色' }, { name: '图片' }],
    colorConfig: {
      title: '背景颜色',
      default: [{ item: '#F5F5F5' }, { item: '#F5F5F5' }],
      color: [{ item: '#F5F5F5' }, { item: '#F5F5F5' }],
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
  bottomBgColor: {
    title: '底部背景',
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
  paddingConfig: {
    title: '内边距',
    isAll: false,
    val: 0,
    min: 0,
    valList: [{ val: 0 }, { val: 10 }, { val: 0 }, { val: 10 }],
  },
  marginConfig: {
    title: '外边距',
    isAll: false,
    val: 0,
    min: 0,
    valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
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
import __asset_bargain02_0 from '@/assets/images/bargain02.png';
import __asset_bargain01_1 from '@/assets/images/bargain01.png';
import Setting from '@/setting';

defineOptions({
  name: 'home_bargain',
  cname: '砍价',
  icon: '#iconzujian-kanjia',
  configName: 'c_home_bargain',
  type: 1,
  // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'bargain',
  // 插件归属组件：经 diy/component_status 白名单放行（配合清单 diy.components 声明）
  pluginComponent: true,
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

const configObj = ref(null);
// 默认初始化数据禁止修改

const pageData = ref({});
const imgUrl = ref('');
const imgBgUrl = ref('');
const tipsColor = ref('');
const tipsColor2 = ref('');
const dividerColor = ref('');
const rightBntTxt = ref('');
const tipTxt = ref('');
const headerBntColor = ref('');
const headerBntColor2 = ref('');
const bntNumber = ref(0);
const styleConfig = ref(0);
const headerBgColorLeft = ref('');
const headerBgColorRight = ref('');
const imgColorUrl = ref('');
const titleConfig = ref(0);
const titleTxtConfig = ref('');
// bottomBgColor: '',
// paddingConfig: {
//   val: 0,
//   valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
// },
// marginConfig: {
//   val: 0,
//   valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
// },
const titleText = ref('');
const titleTabVal = ref(0);
const checkboxInfo = ref([]);
const imgRadius = ref(0);
const goodsName = ref('');
const goodsNameColor = ref('');
const goodsPriceColor = ref('');
const toneConfig = ref(0);
const goodsBntColorLeft = ref('');
const goodsBntColorRight = ref('');
const goodStyleConfig = ref(0);
const goodsBntTxtColor = ref('');
const bargainConfig = ref(0);
const numberConfig = ref(1);
const titleColor = ref('');
const titleNumber = ref(0);
const joinNumColor = ref('');
const joinNumColor2 = ref('');
const bargainPriceColor = ref('');
const joinBgColorLeft = ref('');
const joinBgColorRight = ref('');
const themeColor = ref('');
const themeColor2 = ref('');
const bgColor = ref('');

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
  configObj.value = data;
  let isLegacyPadding = !data.paddingConfig;
  let isLegacyMargin = !data.marginConfig;

  for (let key in defaultConfig) {
    if (data[key] == undefined) {
      data[key] = JSON.parse(JSON.stringify(defaultConfig[key]));
    }
  }

  if (isLegacyPadding) {
    if (data.topConfig) data.paddingConfig.valList[0].val = data.topConfig.val;
    if (data.bottomConfig) data.paddingConfig.valList[2].val = data.bottomConfig.val;
    if (data.prConfig) {
      data.paddingConfig.valList[1].val = data.prConfig.val;
      data.paddingConfig.valList[3].val = data.prConfig.val;
    }
  }
  if (isLegacyMargin) {
    if (data.mbConfig) data.marginConfig.valList[0].val = data.mbConfig.val;
  }
  let bgColorLeft = data.moduleColor.color[0].item;
  let bgColorRight = data.moduleColor.color[1].item;
  bgColor.value = `linear-gradient(90deg,${bgColorLeft} 0%,${bgColorRight} 100%)`;
  if (data.mbConfig || data.marginConfig) {
    imgUrl.value = data.imgConfig.url;
    imgBgUrl.value = data.imgBgConfig.url;
    imgColorUrl.value = data.imgColorConfig.url;
    tipsColor.value = data.tipsColor.color[0].item;
    tipsColor2.value = data.tipsColor2.color[0].item;
    dividerColor.value = data.dividerColor.color[0].item;
    rightBntTxt.value = data.rightBntConfig.value;
    tipTxt.value = data.tipTxtConfig.value;
    headerBntColor.value = data.headerBntColor.color[0].item;
    headerBntColor2.value = data.headerBntColor2.color[0].item;
    bntNumber.value = data.bntNumber.val;
    styleConfig.value = data.styleConfig.tabVal;
    headerBgColorLeft.value =
      data.headerBgColor && data.headerBgColor.color[0] ? data.headerBgColor.color[0].item : '#F62C2C';
    headerBgColorRight.value =
      data.headerBgColor && data.headerBgColor.color[1] ? data.headerBgColor.color[1].item : '#F96E29';
    titleConfig.value = data.titleConfig.tabVal;
    titleTxtConfig.value = data.titleTxtConfig.value;

    let tabVal = data.titleText.tabVal;
    titleTabVal.value = tabVal;
    titleText.value = data.titleText.tabList[tabVal].style;
    checkboxInfo.value = data.checkboxInfo.type;
    let filletImg = data.filletImg.type;
    let filletValImg = data.filletImg.val;
    let valListImg = data.filletImg.valList;
    imgRadius.value = filletImg
      ? valListImg[0].val + 'px ' + valListImg[1].val + 'px ' + valListImg[3].val + 'px ' + valListImg[2].val + 'px'
      : filletValImg + 'px';
    let goodsTabVal = data.goodsName.tabVal;
    goodsName.value = data.goodsName.tabList[goodsTabVal].style;
    goodsNameColor.value = data.goodsNameColor.color[0].item;
    goodsPriceColor.value = data.goodsPriceColor.color[0].item;
    toneConfig.value = data.toneConfig.tabVal;
    goodsBntColorLeft.value = data.goodsBntColor.color[0].item;
    goodsBntColorRight.value = data.goodsBntColor.color[1].item;
    goodStyleConfig.value = data.goodStyleConfig.tabVal;
    goodsBntTxtColor.value = data.goodsBntTxtColor.color[0].item;
    bargainConfig.value = data.bargainConfig.tabVal;
    numberConfig.value = data.numberConfig.val;
    titleColor.value = data.titleColor.color[0].item;
    titleNumber.value = data.titleNumber.val;
    joinNumColor.value = data.styleConfig.tabVal ? data.joinNumColor.color[0].item : data.joinNumColor2.color[0].item;
    joinNumColor2.value = data.joinNumColor.color[0].item;
    bargainPriceColor.value = data.bargainPriceColor.color[0].item;
    joinBgColorLeft.value = data.joinBgColor.color[0].item;
    joinBgColorRight.value = data.joinBgColor.color[1].item;
    themeColor.value = `linear-gradient(90deg,${props.colorStyle.theme} 0%,${props.colorStyle.gradient} 100%)`;
    themeColor2.value = `linear-gradient(270deg,${props.colorStyle.theme} 0%,${props.colorStyle.gradient} 100%)`;
  }
}
</script>

<style scoped lang="scss">
.seckill-box {
  display: inline-block;
  width: -webkit-fill-available;
  .hd {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-repeat: no-repeat;
    background-size: 100% 100%;
    width: 100%;
    height: 48px;
    padding: 0 12px;
    .right {
      color: #fff;
      font-size: 12px;
      .iconfont {
        font-size: 12px;
      }
    }
    .left {
      display: flex;
      align-items: center;
      .text {
        font-size: 16px;
      }
      .line {
        width: 1px;
        height: 14px;
        background: #dddddd;
        margin: 0 10px;
      }
      img {
        width: 70px;
        height: 16px;
      }
      .tips {
        font-size: 13px;
        color: #fff;
        font-weight: 400;
      }
    }
  }
  .list-wrapper {
    display: flex;
    justify-content: center;
    overflow: hidden;
    padding: 10px;
    width: 100%;
    &.on {
      display: block;
    }
    &.on2 {
      flex-wrap: wrap;
      justify-content: flex-start;
    }
    &.on3 {
      justify-content: flex-start;
      padding-right: 0;
    }
    .itemTwo,
    .itemThree {
      width: 48%;
      position: relative;
      margin-right: 11px;
      margin-top: 15px;

      .item {
        height: 50px;
      }

      .item2 {
        height: 30px;
      }

      &:nth-child(1) {
        margin-top: 0;
      }

      &:nth-child(2) {
        margin-top: 0;
      }

      &:nth-of-type(2n) {
        margin-right: 0;
      }

      .empty-box {
        width: 100%;
        height: 162px;
        background-color: #f3f9ff;
        img {
          width: 64px;
          height: 50px;
          display: block;
        }
      }
      .title {
        font-size: 14px;
        color: #333333;
        margin-top: 8px;
      }
      .price {
        font-weight: 600;
        font-size: 12px;
        &.on {
          margin-top: 8px;
        }
        .num {
          font-size: 15px;
        }
      }
      .yprice {
        font-size: 11px;
        text-decoration: line-through;
        &.on {
          margin-top: 9px;
        }
      }
      .bnt {
        width: 57px;
        height: 26px;
        border-radius: 13px;
        text-align: center;
        line-height: 26px;
        position: absolute;
        right: 0;
        bottom: 0;
        font-size: 12px;
        color: #ffffff;
        &.on {
          bottom: -4px;
        }
      }
    }

    .itemThree {
      width: 112px;
      margin-top: 0;
      margin-right: 10px !important;
      .item {
        height: 45px;
      }
      .item2 {
        height: 29px;
      }
      .empty-box {
        height: 112px;
        width: 112px;
      }
      .title {
        font-size: 13px;
        margin-top: 6px;
      }
      .joinNum {
        width: 76px;
        height: 13px;
        border-radius: 7px;
        font-size: 10px;
        text-align: center;
        line-height: 13px;
        margin-top: 3px;
      }
      .price {
        font-size: 11px;
        margin-top: 3px;
        &.on {
          margin-top: 6px;
        }
      }
    }

    .itemOne {
      position: relative;

      & ~ .itemOne {
        margin-top: 15px;
      }
      .empty-box {
        width: 140px;
        height: 140px;
        margin-right: 10px;
        background-color: #f3f9ff;
        img {
          width: 64px;
          height: 50px;
          display: block;
        }
      }
      .text {
        flex: 1;
        .top {
          height: 98px;
          .num {
            font-size: 12px;
            color: #e93323;
            .iconfont {
              font-size: 12px;
              margin-right: 2px;
            }
          }
        }
        .bottom {
          margin-top: 8px;
          height: 40px;
        }
        .name {
          font-size: 14px;
          color: #333333;
          margin-bottom: 9px;
        }
        .price {
          font-size: 12px;
          color: #e93323;
          .label {
            margin-right: 2px;
          }
          .num {
            font-size: 16px;
            font-weight: 500;
            font-family: SemiBold;
          }
        }
        .yprice {
          color: #999999;
          font-size: 11px;
          text-decoration: line-through;
        }
        .bnt {
          width: 72px;
          height: 28px;
          background: linear-gradient(90deg, #ff7931 0%, #e93323 100%);
          border-radius: 25px;
          text-align: center;
          line-height: 28px;
          color: #ffffff;
          font-size: 12px;
          position: absolute;
          right: 0;
          bottom: 0;
        }
      }
    }

    .list-item {
      width: 31.47%;
      margin-top: 10px;

      & ~ .list-item {
        margin-left: 9px;
      }

      &:nth-of-type(3n-2) {
        margin-left: 0;
      }

      &:nth-child(1),
      &:nth-child(2),
      &:nth-child(3) {
        margin-top: 0;
      }

      .img-box {
        border-radius: 6px;
        position: relative;
        width: 100%;
        height: 106px;

        .empty-box {
          background-color: #f3f9ff;
          img {
            width: 65px;
            height: 50px;
            display: block;
          }
        }
      }
      .title {
        margin-top: 8px;
        font-size: 13px;
        color: #333;
      }
      .price {
        position: relative;
        color: #fff;
        font-size: 11px;

        .lable {
          font-size: 11px;
          font-weight: 600;
          margin-left: 2px;
        }

        .num {
          font-size: 15px;
          font-weight: 600;
        }

        img {
          width: 12px;
          height: 22px;
          display: block;
          position: absolute;
          left: -4px;
          top: 0;
        }
      }
      .yprice {
        color: #999;
        font-size: 12px;
        text-decoration: line-through;
      }
    }
  }
}
</style>
