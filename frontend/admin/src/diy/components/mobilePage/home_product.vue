<template>
  <common_wrapper :config="configObj">
    <div class="home_product">
      <div class="hd_nav" v-if="styleConfig == 0">
        <div class="item" :class="index == tabCur ? 'active' : ''" v-for="(item, index) in navlist" :key="index">
          <p
            class="title"
            :style="{ color: index == tabCur ? (toneConfig ? textColor2 : colorStyle.theme) : '#282828' }"
          >
            {{ item.chiild[0].val || '标题' }}
          </p>
          <span
            class="label"
            :style="{ background: index == tabCur ? (toneConfig ? decorateColor : themeColor) : '' }"
            v-if="item.chiild[1].val"
            >{{ item.chiild[1].val || '标题简介' }}</span
          >
        </div>
      </div>
      <div class="menus" :class="styleConfig == 2 ? 'on' : ''" v-else>
        <template v-if="styleConfig == 1">
          <div
            class="item"
            :class="index == tabCur ? 'on' : ''"
            v-for="(item, index) in navlist"
            :key="index"
            :style="{
              color: index == tabCur ? (toneConfig ? textColor : '#333') : '#282828',
            }"
          >
            {{ item.chiild[0].val || '标题'
            }}<span
              :style="{
                background: toneConfig ? decorateColor : themeColor,
              }"
            ></span>
          </div>
        </template>
        <template v-if="styleConfig == 2">
          <div
            class="item"
            :class="index == tabCur ? 'on3' : ''"
            v-for="(item, index) in navlist"
            :key="index"
            :style="{
              color: index == tabCur ? (toneConfig ? textColor2 : colorStyle.theme) : '#282828',
            }"
          >
            {{ item.chiild[0].val || '标题'
            }}<span
              :style="{
                borderColor: toneConfig ? decorateColor2 : colorStyle.theme,
              }"
            ></span>
          </div>
        </template>
        <template v-if="styleConfig == 3">
          <div
            class="item"
            :class="index == tabCur ? 'on2' : ''"
            v-for="(item, index) in navlist"
            :key="index"
            :style="{
              color: index == tabCur ? (toneConfig ? textColor3 : '#fff') : '#282828',
              background: index == tabCur ? (toneConfig ? decorateColor : themeColor) : '',
            }"
          >
            {{ item.chiild[0].val || '标题' }}
          </div>
        </template>
        <template v-if="styleConfig == 4">
          <div class="item pic" v-for="(item, index) in navlist" :key="index">
            <div
              class="pictrue acea-row row-center-wrapper"
              :style="{
                borderColor: index == tabCur ? (toneConfig ? decorateColorLeft : colorStyle.theme) : '#EEEEEE',
              }"
            >
              <img class="img" :src="item.image" v-if="item.image" />
              <img src="@diy/assets/images/shan.png" v-else />
            </div>
            <div
              class="title"
              :style="{
                color: index == tabCur ? (toneConfig ? textColor3 : '#fff') : '#282828',
                background: index == tabCur ? (toneConfig ? decorateColor : themeColor) : '',
              }"
            >
              {{ item.chiild[0].val || '标题' }}
            </div>
          </div>
        </template>
      </div>
      <div class="list-wrapper">
        <div class="item" v-for="(item, index) in list" :key="index">
          <div class="img-box">
            <img
              class="img"
              v-if="item.image"
              :src="item.image"
              alt=""
              :style="{
                borderRadius: bgRadius,
              }"
            />
            <div
              v-else
              class="empty-box"
              :style="{
                borderRadius: bgRadius,
              }"
            >
              <img src="@diy/assets/images/shan.png" />
            </div>
          </div>
          <div class="info" :style="{ borderRadius: bgRadius2 }">
            <div class="title line2">
              {{ item.store_name || '蓝牙音乐手表 | Jeep智能表蓝牙通话健康管理 P07' }}
            </div>
            <div class="pictrue">
              <img src="@diy/assets/images/goods01.png" />
            </div>
            <div class="price">
              <div class="num" :style="{ color: goodsPriceColor }">
                <span>￥</span>{{ item.price ? $HandlePrice(item.price, 0) : 77
                }}<span>{{ item.price ? $HandlePrice(item.price, 1) : '' }}</span>
              </div>
              <img src="@diy/assets/images/goods02.png" />
            </div>
            <div class="sales">已售{{ item.sales || 0 }}件</div>
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
  </common_wrapper>
</template>

<script>
// 模块级具名导出：DIY 编辑器新增组件时经聚合层取默认配置工厂
// （原 Vue2 时代通过组件选项 data().defaultConfig 暴露；timestamp 依赖实例 props，故为工厂函数）
export const getDefaultConfig = (num) => ({
  cname: '商品选项卡',
  desc: '商品选项卡',
  name: 'promotionList',
  timestamp: num,
  isHide: false,
  setUp: {
    tabVal: 0,
  },
  titleLeft: '展示设置',
  titleTab: '选项卡设置',
  titleRight: '选项卡样式',
  titleCurrency: '通用样式',
  titleCart: '购物车按钮',
  styleConfig: {
    title: '选择风格',
    tabVal: 1,
    tabList: [
      {
        name: '样式一',
      },
      {
        name: '样式二',
      },
      {
        name: '样式三',
      },
      {
        name: '样式四',
      },
      {
        name: '样式五',
      },
    ],
  },
  slideConfig: {
    title: '滑动置顶',
    tabVal: 1,
    tabList: [
      {
        name: '启用',
      },
      {
        name: '不启用',
      },
    ],
  },
  tabConfig: {
    title: '点击下方选项卡可进行编辑；鼠标拖拽版块可调整顺序',
    max: '',
    tabCur: 0,
    classList: [],
    list: [
      {
        chiild: [
          {
            title: '标题',
            val: '新品推荐',
            max: 4,
            pla: '选填，不超过四个字',
          },
          {
            title: '简介',
            val: '最新出炉',
            max: 4,
            pla: '选填，不超过四个字',
          },
        ],
        image: '',
        tabVal: 1,
        brandConfig: {
          brandVal: [],
        },
        selectConfig: {
          activeValue: [],
        },
        goodsLabel: {
          activeValue: [],
          list: [],
        },
        goodsSort: 0,
        numConfig: {
          val: 6,
        },
        goodsList: {
          max: 20,
          list: [],
        },
        productList: {
          list: [],
        },
      },
    ],
  },
  cartConfig: {
    title: '是否显示',
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
  bntConfig: {
    title: '按钮效果',
    tabVal: 1,
    tabList: [
      {
        name: '进入商品详情页',
      },
      {
        name: '商品加购',
      },
    ],
  },
  bntStyleConfig: {
    typeFrom: 'bnt',
    title: '按钮样式',
    tabVal: 0,
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
        item: '#E93323',
      },
    ],
    color: [
      {
        item: '#E93323',
      },
    ],
  },
  decorateColor: {
    title: '装饰元素',
    default: [
      {
        item: '#E93323',
      },
      {
        item: '#FF7931',
      },
    ],
    color: [
      {
        item: '#E93323',
      },
      {
        item: '#FF7931',
      },
    ],
  },
  decorateColor2: {
    title: '装饰元素',
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
  textColor: {
    title: '选中文字',
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
  textColor2: {
    title: '选中文字',
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
  textColor3: {
    title: '选中文字',
    default: [
      {
        item: '#FFFFFF',
      },
    ],
    color: [
      {
        item: '#FFFFFF',
      },
    ],
  },
  toneCartConfig: {
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
  bntBgColor: {
    title: '按钮颜色',
    name: 'bntBgColor',
    default: [
      {
        item: '#E93323',
      },
      {
        item: '#FF7931',
      },
    ],
    color: [
      {
        item: '#E93323',
      },
      {
        item: '#FF7931',
      },
    ],
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
  componentBgConfig: {
    title: '背景设置',
    tabVal: 0,
    tabList: [{ name: '颜色' }, { name: '图片' }],
    colorConfig: {
      title: '背景颜色',
      default: [{ item: '#FFFFFF' }, { item: '#FFFFFF' }],
      color: [{ item: '#FFFFFF' }, { item: '#FFFFFF' }],
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
  fillet: {
    title: '背景圆角',
    type: 0,
    list: [
      { val: '全部', icon: 'iconcaozuo-zhengti' },
      { val: '单个', icon: 'iconcaozuo-bianjiao' },
    ],
    valName: '圆角值',
    val: 0,
    min: 0,
    valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
  },
});
</script>

<script setup>
import { ref, watch, onMounted, nextTick } from 'vue';
import { useMobildConfigStore } from '@diy/store/mobildConfig';

defineOptions({
  name: 'home_product',
  cname: '商品选项卡',
  configName: 'c_home_product',
  desc: '商品选项卡',
  icon: '#iconzujian-shangpinxuanxiangka',
  type: 0,
  // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'promotionList',
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

// 默认初始化数据禁止修改

const configObj = ref(null);
const navlist = ref([]);
const imgStyle = ref('');
const tabCur = ref(0);
const list = ref([]);
const pageData = ref({});
const styleConfig = ref(0);
const toneConfig = ref(0);
const textColor = ref('');
const textColor2 = ref('');
const textColor3 = ref('');
const decorateColor = ref('');
const decorateColor2 = ref('');
const decorateColorLeft = ref('');
// bgColor:'',
const bottomBgColor = ref('');
const topConfig = ref(0);
const bottomConfig = ref(0);
const prConfig = ref(0);
const bgRadius = ref(0);
const bgRadius2 = ref(0);
const themeColor = ref('');
const cartConfig = ref(0);
const toneCartConfig = ref(0);
const bntBgColor = ref('');
const bntStyleConfig = ref(0);
const goodsPriceColor = ref('');

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
    const data = mobildConfigStore.defaultArray[nVal];
    setConfig(data);
  },
  { deep: true },
);
watch(
  () => mobildConfigStore.defaultArray,
  (nVal, oVal) => {
    const data = mobildConfigStore.defaultArray[props.num];
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
  for (let key in defaultConfig) {
    if (data[key] == undefined) {
      data[key] = JSON.parse(JSON.stringify(defaultConfig[key]));
    }
  }
  styleConfig.value = data.styleConfig.tabVal;
  cartConfig.value = data.cartConfig.tabVal;
  bntStyleConfig.value = data.bntStyleConfig.tabVal;
  toneCartConfig.value = data.toneCartConfig.tabVal;
  let bntBgColorLeft = data.bntBgColor.color[0].item;
  let bntBgColorRight = data.bntBgColor.color[1].item;
  bntBgColor.value = `linear-gradient(90deg,${bntBgColorLeft} 0%,${bntBgColorRight} 100%)`;
  toneConfig.value = data.toneConfig.tabVal;
  textColor.value = data.textColor.color[0].item;
  textColor2.value = data.textColor2.color[0].item;
  textColor3.value = data.textColor3.color[0].item;
  let decorateColorRight = data.decorateColor.color[1].item;
  decorateColorLeft.value = data.decorateColor.color[0].item;
  goodsPriceColor.value = toneCartConfig.value ? data.goodsPriceColor.color[0].item : '#E93323';
  decorateColor.value = `linear-gradient(90deg,${decorateColorLeft.value} 0%,${decorateColorRight} 100%)`;
  decorateColor2.value = data.decorateColor2.color[0].item;
  themeColor.value = `linear-gradient(90deg,${props.colorStyle.theme} 0%,${props.colorStyle.gradient} 100%)`;
  // let bgColorLeft =  data.moduleColor.color[0].item;
  // let bgColorRight =  data.moduleColor.color[1].item;
  // this.bgColor = `linear-gradient(90deg,${bgColorLeft} 0%,${bgColorRight} 100%)`;
  bottomBgColor.value = data.bottomBgColor.color[0].item;
  configObj.value = data;
  // 兼容旧数据
  if (!data.paddingConfig) {
    let paddingConfig = {
      title: '内边距',
      isAll: false,
      val: 0,
      min: 0,
      valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
    };
    if (data.topConfig) paddingConfig.valList[0].val = data.topConfig.val;
    if (data.prConfig) {
      paddingConfig.valList[1].val = data.prConfig.val;
      paddingConfig.valList[3].val = data.prConfig.val;
    }
    if (data.bottomConfig) paddingConfig.valList[2].val = data.bottomConfig.val;
    configObj.value['paddingConfig'] = paddingConfig;
  }
  if (!data.marginConfig) {
    let marginConfig = {
      title: '外边距',
      isAll: false,
      val: 0,
      min: 0,
      valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
    };
    if (data.mbConfig) marginConfig.valList[0].val = data.mbConfig.val;
    configObj.value['marginConfig'] = marginConfig;
  }
  let fillet = data.fillet.type;
  let filletVal = data.fillet.val;
  let valList = data.fillet.valList;
  bgRadius.value = fillet
    ? valList[0].val + 'px ' + valList[1].val + 'px 0 0'
    : filletVal + 'px ' + filletVal + 'px 0 0';
  bgRadius2.value = fillet
    ? '0 0 ' + valList[3].val + 'px ' + valList[2].val + 'px'
    : '0 0 ' + filletVal + 'px ' + filletVal + 'px';
  navlist.value = data.tabConfig.list;
  tabCur.value = data.tabConfig.tabCur || 0;
  let goods = data.tabConfig.list[tabCur.value];
  if (goods.tabVal == 1 && goods.goodsList.list) {
    list.value = goods.goodsList.list.length ? goods.goodsList.list : 2;
  } else if (goods.goodsList.list) {
    list.value = goods.productList.list.length ? goods.productList.list : 2;
  } else {
    list.value = goods.productList.list.length ? goods.productList.list : 2;
  }
}
</script>

<style scoped lang="scss">
.mobile-page {
  display: inline-block;
  width: -webkit-fill-available;
}
.menus {
  display: flex;
  align-items: center;
  width: 10000%;
  cursor: pointer;
  padding-left: 12px;
  padding: 9px 0;

  &.on {
    padding-bottom: 14px;
  }

  .title {
    color: #333;
    font-size: 12px;
    margin-top: 4px;
    height: 20px;
    border-radius: 20px;
    padding: 0 5px;
    line-height: 20px;
    text-align: center;
    white-space: nowrap;
  }

  .item {
    position: relative;
    color: #333;
    font-size: 14px;
    margin-right: 28px;
    z-index: 9;

    &.pic {
      margin-right: 15px;
    }

    .pictrue {
      width: 46px;
      height: 46px;
      border: 1px solid #eeeeee;
      border-radius: 50%;
      background: #f3f9ff;
      margin: 0 auto;

      .img {
        width: 42px;
        height: 42px;
        border-radius: 50%;
      }

      img {
        width: 27px;
      }
    }

    &.on {
      font-size: 16px;
      font-weight: 600;

      span {
        display: block;
        position: absolute;
        left: 50%;
        bottom: 4px;
        width: 100%;
        height: 4px;
        border-radius: 100px;
        transform: translateX(-50%);
        background: #fff;
        background: linear-gradient(90deg, #e93323 0%, #ff7931 100%);
        z-index: -1;
      }
    }

    &.on2 {
      height: 24px;
      text-align: center;
      line-height: 24px;
      color: #fff;
      background: linear-gradient(90deg, #e93323 0%, #ff7931 100%);
      border-radius: 50px;
      padding: 0 6px;
    }

    &.on3 {
      font-size: 16px;
      font-weight: 600;
      color: #e93323;

      span {
        position: absolute;
        width: 30px;
        height: 30px;
        border: 3px solid #e93323;
        border-left: 3px solid transparent !important;
        border-top: 3px solid transparent !important;
        border-right: 3px solid transparent !important;
        border-radius: 50%;
        bottom: -4px;
        left: 50%;
        transform: translateX(-50%);
      }
    }
  }
}

.home_product {
  overflow: hidden;

  .hd_nav {
    display: flex;
    height: 65px;
    overflow: hidden;
    padding: 10px 0;

    .item {
      display: flex;
      flex-direction: column;
      justify-content: center;
      margin-right: 37px;

      .title {
        white-space: nowrap;
        font-size: 15px;
        color: #282828;
        text-align: center;
      }

      .label {
        width: 56px;
        height: 19px;
        line-height: 19px;
        text-align: center;
        background: transparent;
        border-radius: 10px;
        color: #999999;
        font-size: 11px;
      }

      &.active {
        .title {
          color: #ff4444;
        }

        .label {
          color: #fff;
          background: linear-gradient(270deg, rgba(255, 84, 0, 1) 0%, rgba(255, 0, 0, 1) 100%);
        }
      }
    }
  }

  .list-wrapper {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;

    .item {
      width: 48.5%;
      margin-bottom: 10px;
      position: relative;

      .jia {
        width: 22px;
        height: 22px;
        background-color: #e93323;
        border-radius: 50%;
        position: absolute;
        right: 10px;
        bottom: 8px;

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
        width: 100%;
        height: 173px;

        .img {
          width: 100%;
          height: 100%;
        }

        img,
        .box {
          width: 65px;
          height: 50px;
        }

        .empty-box {
          background: #f3f9ff;
        }

        .box {
          background: #d8d8d8;
        }

        .label {
          position: absolute;
          left: 0;
          top: 0;
          width: 46px;
          height: 22px;
          border-radius: 10px 0px 10px 0px;
          color: #fff;
          font-size: 13px;
          text-align: center;
          line-height: 22px;
        }
      }

      .info {
        padding: 7px 10px;
        background: #fff;
        border-radius: 0px 0px 10px 10px;

        .pictrue {
          // width: 99px;
          height: 14px;
          margin-top: 4px;

          img {
            // width: 100%;
            height: 100%;
          }
        }

        .title {
          font-size: 14px;
          color: #282828;
        }

        .sales {
          color: #999;
          font-size: 11px;
        }

        .price {
          display: flex;
          align-items: center;
          margin-top: 6px;

          img {
            width: 70px;
            height: 15px;
          }

          .num {
            font-size: 20px;
            margin-right: 4px;
            font-family: SemiBold;
            color: #e93323;
            span {
              font-size: 12px;
            }
          }

          .label {
            width: 16px;
            height: 18px;
            margin-left: 5px;
            text-align: center;
            line-height: 18px;
            font-size: 11px;

            &.on {
              margin-left: 0;
            }
          }
        }
      }
    }
  }
}
</style>
