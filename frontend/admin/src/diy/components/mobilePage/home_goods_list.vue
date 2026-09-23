<template>
  <div>
    <common_wrapper :config="configObj">
      <div class="home_product">
        <!-- 单列 -->
        <template v-if="styleConfig == 0">
          <div class="list-wrapper itemA">
            <div
              class="item"
              v-for="(item, index) in list"
              :key="index"
              :index="index"
              :style="{
                background: bgColor,
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
        <!-- 二列 -->
        <template v-else-if="styleConfig == 1">
          <div class="list-wrapper itemC">
            <div
              class="item"
              v-for="(item, index) in list"
              :key="index"
              :index="index"
              :style="{
                background: bgColor,
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
                    : ((checkboxInfo.length == 1 && checkboxInfo.indexOf(4) != -1) || !checkboxInfo.length) &&
                      !cartConfig
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
        <!-- 大图 -->
        <template v-else-if="styleConfig == 4">
          <div class="listBig">
            <div
              class="itemBig"
              v-for="(item, index) in list"
              :key="index"
              :style="{
                background: bgColor,
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
              <div
                class="conter"
                :class="
                  ((checkboxInfo.length == 1 &&
                    (checkboxInfo.indexOf(1) != -1 ||
                      checkboxInfo.indexOf(3) != -1 ||
                      checkboxInfo.indexOf(4) != -1 ||
                      checkboxInfo.indexOf(5) != -1)) ||
                    !checkboxInfo.length) &&
                  !cartConfig
                    ? 'on'
                    : ''
                "
              >
                <div
                  class="name"
                  v-if="checkboxInfo.indexOf(0) != -1"
                  :style="{
                    fontWeight: goodsName,
                    color: toneConfig ? goodsNameColor : '#333',
                  }"
                >
                  {{ item.store_name || '商品名称商品商名称商品商…' }}
                </div>
                <img v-if="checkboxInfo.indexOf(1) != -1" src="@diy/assets/images/goods01.png" />
                <div class="price acea-row row-middle">
                  <div
                    class="num"
                    v-if="checkboxInfo.indexOf(2) != -1"
                    :style="{
                      color: toneConfig ? goodsPriceColor : colorStyle.theme,
                    }"
                  >
                    <span>￥</span>{{ item.price ? $HandlePrice(item.price, 0) : 77
                    }}<span>{{ item.price ? $HandlePrice(item.price, 1) : '' }}</span>
                  </div>
                  <img v-if="checkboxInfo.indexOf(5) != -1" src="@diy/assets/images/goods02.png" />
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
        <template v-else-if="styleConfig == 3">
          <div
            class="listCross acea-row row-between-wrapper"
            :style="{
              background: bgColor,
              borderRadius: bgRadius,
            }"
          >
            <div class="item acea-row row-middle" v-for="(item, index) in list" :key="index" :index="index">
              <div
                class="pictrue acea-row row-center-wrapper"
                :style="{
                  borderRadius: imgRadius,
                }"
              >
                <img
                  class="img"
                  v-if="item.image"
                  :src="item.image"
                  alt=""
                  :style="{
                    borderRadius: imgRadius,
                  }"
                />
                <img v-else src="@diy/assets/images/shan.png" />
              </div>
              <div class="text">
                <div
                  class="name"
                  v-if="checkboxInfo.indexOf(0) != -1"
                  :style="{
                    fontWeight: goodsName,
                    color: toneConfig ? goodsNameColor : '#333',
                  }"
                >
                  <div class="line2">{{ item.store_name || '这里是标题这里是标题这...' }}</div>
                </div>
                <div
                  class="price"
                  v-if="checkboxInfo.indexOf(2) != -1"
                  :style="{
                    color: toneConfig ? goodsPriceColor : colorStyle.theme,
                  }"
                >
                  <span>￥</span>{{ item.price ? $HandlePrice(item.price, 0) : 77
                  }}<span>{{ item.price ? $HandlePrice(item.price, 1) : '' }}</span>
                </div>
              </div>
            </div>
          </div>
        </template>
        <!-- 三列 -->
        <template v-else>
          <div
            class="list-wrapper itemB"
            :class="styleConfig == 5 ? 'itemD' : ''"
            :style="{
              background: bgColor,
              borderRadius: bgRadius,
            }"
          >
            <div class="list">
              <div class="item" v-for="(item, index) in list" :key="index" :index="index">
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
  </div>
</template>

<script>
// 模块级具名导出：DIY 编辑器新增组件时经聚合层取默认配置工厂
// （原 Vue2 时代通过组件选项 data().defaultConfig 暴露；timestamp 依赖实例 props，故为工厂函数）
export const getDefaultConfig = (num) => ({
  cname: '商品列表',
  desc: '商品列表介绍',
  name: 'goodList',
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
  titleLeft: '列表设置',
  titleGoods: '商品设置',
  titleContents: '显示内容',
  titleCart: '购物车按钮',
  titleRight: '商品样式',
  titleCurrency: '通用样式',
  styleConfig: {
    title: '选择风格',
    tabVal: 1,
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
        name: '两列展示',
      },
      {
        name: '大图展示',
      },
      {
        name: '左右滑动',
      },
    ],
  },
  typeConfig: {
    title: '选择方式',
    activeValue: 1,
    list: [
      {
        activeValue: 1,
        title: '指定商品',
      },
      {
        activeValue: 3,
        title: '指定分类',
      },
      {
        activeValue: 4,
        title: '商品标签',
      },
    ],
  },
  goodsList: {
    max: 20,
    list: [],
  },
  goodsSort: {
    title: '商品排序',
    tabVal: 1,
    tabList: [
      {
        name: '综合',
      },
      {
        name: '销量',
      },
      {
        name: '价格',
      },
    ],
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
  checkboxInfo: {
    title: '展示信息',
    name: 'checkboxInfo',
    type: [0, 1, 2, 3, 4, 5],
    list: [
      {
        id: 0,
        name: '商品名称',
      },
      {
        id: 1,
        name: '商品标签',
      },
      {
        id: 2,
        name: '商品价格',
      },
      {
        id: 3,
        name: '商品销量',
      },
      {
        id: 4,
        name: '商品评分',
      },
      {
        id: 5,
        name: '会员价格',
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
    title: '按钮样式',
    tabVal: 0,
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
  soldNumColor: {
    title: '已售数量',
    name: 'soldNumColor',
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
  scoreColor: {
    title: '评分颜色',
    name: 'scoreColor',
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
    valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
  },
  marginConfig: {
    title: '外边距',
    isAll: false,
    val: 0,
    min: 0,
    valList: [{ val: 0 }, { val: 10 }, { val: 0 }, { val: 10 }],
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
  goodsLabel: {
    title: '商品标签',
    activeValue: [],
    list: [],
  },
  productList: {
    title: '商品列表',
    list: [],
  },
});
</script>

<script setup>
import { ref, watch, onMounted, nextTick } from 'vue';
import { useMobildConfigStore } from '@diy/store/mobildConfig';

defineOptions({
  name: 'home_goods_list',
  cname: '商品列表',
  configName: 'c_home_goods_list',
  icon: '#iconzujian-shangpinliebiao',
  type: 0,
  // 0 基础组件 1 营销组件 2工具组件 3 商品组件 4 个人中心组件
  defaultName: 'goodList',
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
const configObj = ref(null);
const bgRadius = ref(0);
const bgRadius2 = ref(0);
const themeColor = ref('');

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
  for (let key in defaultConfig) {
    if (data[key] == undefined) {
      data[key] = defaultConfig[key];
    }
  }
  styleConfig.value = data.styleConfig.tabVal;
  checkboxInfo.value = data.checkboxInfo.type;
  cartConfig.value = data.cartConfig.tabVal;
  bntStyleConfig.value = data.bntStyleConfig.tabVal;
  let filletImg = data.filletImg.type;
  let filletValImg = data.filletImg.val;
  let valListImg = data.filletImg.valList;
  imgRadius.value = filletImg
    ? valListImg[0].val + 'px ' + valListImg[1].val + 'px ' + valListImg[3].val + 'px ' + valListImg[2].val + 'px'
    : filletValImg + 'px';
  imgRadius2.value = filletImg
    ? valListImg[0].val + 'px ' + valListImg[1].val + 'px 0 0'
    : filletValImg + 'px ' + filletValImg + 'px 0 0';
  let goodsTabVal = data.goodsName.tabVal;
  goodsName.value = data.goodsName.tabList[goodsTabVal].style;
  toneConfig.value = data.toneConfig.tabVal;
  goodsNameColor.value = data.goodsNameColor.color[0].item;
  goodsPriceColor.value = data.goodsPriceColor.color[0].item;
  soldNumColor.value = data.soldNumColor.color[0].item;
  scoreColor.value = data.scoreColor.color[0].item;
  toneCartConfig.value = data.toneCartConfig.tabVal;
  let bntBgColorLeft = data.bntBgColor.color[0].item;
  let bntBgColorRight = data.bntBgColor.color[1].item;
  bntBgColor.value = `linear-gradient(90deg,${bntBgColorLeft} 0%,${bntBgColorRight} 100%)`;
  let bgColorLeft = data.moduleColor.color[0].item;
  let bgColorRight = data.moduleColor.color[1].item;
  bgColor.value = `linear-gradient(90deg,${bgColorLeft} 0%,${bgColorRight} 100%)`;
  themeColor.value = `linear-gradient(90deg,${props.colorStyle.theme} 0%,${props.colorStyle.gradient} 100%)`;
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
    data['paddingConfig'] = paddingConfig;
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
    data['marginConfig'] = marginConfig;
  }

  if (!data.bottomBgColor) {
    data['bottomBgColor'] = {
      title: '底部背景',
      default: [{ item: '#f5f5f5' }],
      color: [{ item: '#f5f5f5' }],
    };
  }

  for (let key in defaultConfig) {
    if (data[key] === undefined) {
      data[key] = defaultConfig[key];
    }
  }

  configObj.value = data;
  let fillet = data.fillet.type;
  let filletVal = data.fillet.val;
  let valList = data.fillet.valList;
  bgRadius.value = fillet
    ? valList[0].val + 'px ' + valList[1].val + 'px ' + valList[3].val + 'px ' + valList[2].val + 'px'
    : filletVal + 'px';
  bgRadius2.value = fillet
    ? '0 0 ' + valList[3].val + 'px ' + valList[2].val + 'px'
    : '0 0 ' + filletVal + 'px ' + filletVal + 'px';
  if (data.typeConfig.activeValue == 1) {
    list.value = data.goodsList.list.length ? data.goodsList.list : 4;
  } else {
    list.value = data.productList.list.length ? data.productList.list : 4;
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
.listCross {
  width: 100%;
  background: #fff;
  padding: 16px 12px 6px 12px;
  .item {
    width: 49%;
    min-width: 0;
    margin-bottom: 10px;
    flex-wrap: nowrap;
    .pictrue {
      flex: 0 0 72px;
      width: 72px;
      height: 72px;
      background: #f3f9ff;
      border-radius: 4px;
      margin-right: 10px;
      img {
        width: 26px;
        height: 20px;
        display: block;
      }
      .img {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
      }
    }
    .text {
      flex: 1;
      min-width: 0;
      overflow: hidden;
      .name {
        width: 100%;
        font-size: 13px;
        color: #282828;
        height: 45px;
        overflow: hidden;
        .line2 {
          width: 100%;
        }
      }
      .price {
        font-size: 18px;
        font-weight: 600;
        font-family: SemiBold;
        span {
          font-size: 12px;
        }
      }
    }
  }
}
.listBig {
  width: 100%;
  .itemBig {
    width: 100%;
    margin-bottom: 10px;
    background-color: #fff;
    border-radius: 10px;
    position: relative;
    .bnt {
      width: 48px;
      height: 28px;
      background: linear-gradient(90deg, #e93323 0%, #ff7931 100%);
      border-radius: 25px;
      text-align: center;
      line-height: 28px;
      font-size: 12px;
      color: #fff;
      position: absolute;
      right: 10px;
      bottom: 12px;
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
    .conter {
      padding: 0 12px 12px 12px;
      &.on {
        height: 45px;
      }
      .name {
        margin-top: 10px;
        font-weight: 400;
        color: #333333;
        font-size: 14px;
        padding: 0;
      }
      img {
        // width: 99px;
        height: 14px;
        display: block;
        margin-top: 5px;
      }
      .price {
        margin-top: 8px;
        margin-bottom: 8px;
        img {
          width: 70px;
          height: 15px;
        }
        .num {
          font-size: 20px;
          font-family: SemiBold;
          span {
            font-size: 12px;
          }
        }
      }
      .bottom {
        font-weight: 400;
        color: #999999;
        font-size: 11px;
      }
    }
    .img-box {
      width: 100%;
      height: 180px;
      position: relative;

      img {
        width: 65px;
        height: 50px;
      }
      .img {
        width: 100%;
        height: 100%;
        object-fit: cover;
      }
      .empty-box {
        border-radius: 8px 8px 0 0;
        background: #f3f9ff;
      }
      .label {
        position: absolute;
        top: 0;
        left: 0;
        width: 59px;
        height: 25px;
        line-height: 25px;
        text-align: center;
        color: #fff;
        font-size: 12px;
        border-radius: 8px 0 8px 0;
      }
    }
    .name {
      font-size: 15px;
      font-weight: bold;
      margin-top: 8px;
      padding: 0 10px;
    }
    .coupon {
      width: 16px;
      height: 18px;
      line-height: 18px;
      text-align: center;
      font-size: 12px;
      margin-right: 5px;
      display: inline-block;
    }
    .price {
      font-weight: bold;
      font-size: 12px;
      .num {
        font-size: 18px;
        margin-right: 5px;
      }
    }
  }
}
.home_product {
  overflow: hidden;
  .hd_nav {
    display: flex;
    height: 65px;
    padding: 0 5px;
    .item {
      display: flex;
      flex-direction: column;
      justify-content: center;
      width: 25%;
      .title {
        font-size: 16px;
        color: #282828;
      }
      .label {
        width: 62px;
        height: 18px;
        line-height: 18px;
        text-align: center;
        background: transparent;
        border-radius: 8px;
        color: #999999;
        font-size: 12px;
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

.itemA {
  /*background #fff*/
  .item {
    display: flex;
    width: 100%;
    padding: 10px;
    position: relative;

    .img-box {
      position: relative;
      width: 112px;
      height: 112px;
      img {
        width: 65px;
        height: 50px;
      }
      .empty-box {
        background: #f3f9ff;
      }
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
        .img {
          margin-top: 0;
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
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
    width: 100%;
  }

  .item {
    width: 100%;
    min-width: 0;
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
  display: inline-flex;
  overflow: hidden;
  .list {
    display: flex;
    flex-wrap: nowrap;
    justify-content: center;
    align-items: center;
    gap: 0;
    width: auto;
  }
  .item {
    flex: 0 0 100px;
    width: 100px;
    margin-right: 10px;
    background: unset;
    &:nth-child(3n) {
      margin-right: 10px;
    }
    .img-box {
      height: 100px;
    }
  }
}
.itemC {
  .item {
    background-color: transparent;
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
</style>
