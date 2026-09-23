<template>
  <div>
    <common_wrapper :config="configObj">
      <div class="seckill-box">
        <div
          class="hd"
          :style="
            (styleConfig
              ? 'backgroundImage:url(' + imgBgUrl + ')'
              : `background:linear-gradient(90deg,${headerBgColorLeft} 0%,${headerBgColorRight} 100%)`) +
            ';borderRadius:' +
            bgRadius
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
              class="tips"
              :style="{
                color: styleConfig ? tipsColor : tipsColor2,
              }"
            >
              距离结束
            </div>
            <div class="time">
              <span
                :style="{
                  background: styleConfig ? numberBgColor : numberBgColor2,
                  color: styleConfig ? numberColor : numberColor2,
                }"
                >17</span
              >
              <em
                :style="{
                  color: styleConfig ? numberBgColorLeft : numberBgColorLeft2,
                }"
                >:</em
              >
              <span
                :style="{
                  background: styleConfig ? numberBgColor : numberBgColor2,
                  color: styleConfig ? numberColor : numberColor2,
                }"
                >32</span
              >
              <em
                :style="{
                  color: styleConfig ? numberBgColorLeft : numberBgColorLeft2,
                }"
                >:</em
              >
              <span
                :style="{
                  background: styleConfig ? numberBgColor : numberBgColor2,
                  color: styleConfig ? numberColor : numberColor2,
                }"
                >45</span
              >
            </div>
          </div>
          <div
            class="right"
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
            borderRadius: bgRadius2,
          }"
        >
          <template v-if="goodStyleConfig == 0">
            <div class="itemOne acea-row" v-for="(_item, index) in numberConfig" :key="index">
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
                    橙中爱马仕 黑标新骑士晚季,是你最想拥有的
                  </div>
                  <div
                    class="progressBg"
                    v-if="checkboxInfo.indexOf(1) != -1"
                    :style="{
                      background: toneConfig
                        ? `linear-gradient(45deg,${progressColorLeft} 0%,${progressColorRight} 100%)`
                        : themeColor2,
                    }"
                  >
                    <div class="progressBar">
                      <div
                        class="progress"
                        :style="{
                          background: toneConfig
                            ? `linear-gradient(45deg,${progressColorLeft} 0%,${progressColorRight} 100%)`
                            : themeColor2,
                        }"
                      ></div>
                      <img src="@diy/assets/images/dian2.png" />
                    </div>
                    <div
                      class="progressTxt"
                      :style="{
                        color: toneConfig ? progressTxtColor : colorStyle.theme,
                      }"
                    >
                      已抢33%
                    </div>
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
                      color: toneConfig ? seckillPriceColor : colorStyle.theme,
                    }"
                  >
                    秒杀价<span class="label">¥</span><span class="num">3200.00</span>
                  </div>
                  <div
                    class="yprice"
                    v-if="checkboxInfo.indexOf(3) != -1"
                    :style="{
                      color: goodsPriceColor,
                    }"
                  >
                    ¥1233.00
                  </div>
                </div>
                <div
                  class="bnt"
                  v-if="!seckillConfig"
                  :style="{
                    color: toneConfig ? goodsBntTxtColor : '#fff',
                    background: toneConfig
                      ? `linear-gradient(90deg,${goodsBntColorRight} 0%,${goodsBntColorLeft} 100%)`
                      : themeColor,
                  }"
                >
                  去抢购
                </div>
              </div>
            </div>
          </template>
          <template v-if="goodStyleConfig == 1">
            <div class="itemTwo" v-for="(_item2, index2) in numberConfig" :key="index2">
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
                  (checkboxInfo.indexOf(0) != -1 && checkboxInfo.length == 1 && !seckillConfig) ||
                  (checkboxInfo.indexOf(0) != -1 &&
                    checkboxInfo.indexOf(1) != -1 &&
                    checkboxInfo.length == 2 &&
                    !seckillConfig)
                    ? 'item'
                    : (!checkboxInfo.length || (checkboxInfo.indexOf(1) != -1 && checkboxInfo.length == 1)) &&
                      !seckillConfig
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
                  橙中爱马仕 黑标新骑士...
                </div>
                <div
                  class="price"
                  :class="checkboxInfo.indexOf(3) == -1 && !seckillConfig ? 'on' : ''"
                  v-if="checkboxInfo.indexOf(2) != -1"
                  :style="{
                    color: toneConfig ? seckillPriceColor : colorStyle.theme,
                  }"
                >
                  ¥<span class="num">3200.00</span>
                </div>
                <div
                  class="yprice"
                  :class="checkboxInfo.indexOf(2) == -1 && !seckillConfig ? 'on' : ''"
                  v-if="checkboxInfo.indexOf(3) != -1"
                  :style="{
                    color: goodsPriceColor,
                  }"
                >
                  ¥3699.00
                </div>
                <div
                  class="bnt"
                  :class="checkboxInfo.indexOf(2) == -1 && !seckillConfig ? 'on' : ''"
                  v-if="!seckillConfig"
                  :style="{
                    color: toneConfig ? goodsBntTxtColor : '#fff',
                    background: toneConfig
                      ? `linear-gradient(90deg,${goodsBntColorRight} 0%,${goodsBntColorLeft} 100%)`
                      : themeColor,
                  }"
                >
                  去抢购
                </div>
              </div>
            </div>
          </template>
          <template v-if="goodStyleConfig == 2">
            <div class="list-item" v-for="(_item, index) in numberConfig" :key="index">
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
                橙中爱马仕黑橙...
              </div>
              <div
                class="price"
                v-if="checkboxInfo.indexOf(2) != -1"
                :style="{
                  color: toneConfig ? seckillPriceColor2 : '#fff',
                  background: toneConfig
                    ? `linear-gradient(90deg,${goodsBntColorLeft} 0%,${goodsBntColorRight} 100%)`
                    : themeColor2,
                }"
              >
                <img src="@diy/assets/images/dian.png" /><span>¥</span>350.00
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
            <div class="itemThree" v-for="(_item2, index2) in numberConfig" :key="index2">
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
                  (checkboxInfo.indexOf(0) != -1 && checkboxInfo.length == 1 && !seckillConfig) ||
                  (checkboxInfo.indexOf(0) != -1 &&
                    checkboxInfo.indexOf(1) != -1 &&
                    checkboxInfo.length == 2 &&
                    !seckillConfig)
                    ? 'item'
                    : (!checkboxInfo.length || (checkboxInfo.indexOf(1) != -1 && checkboxInfo.length == 1)) &&
                      !seckillConfig
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
                  橙中爱马仕 黑标新骑士...
                </div>
                <div
                  class="price"
                  :class="checkboxInfo.indexOf(3) == -1 && !seckillConfig ? 'on' : ''"
                  v-if="checkboxInfo.indexOf(2) != -1"
                  :style="{
                    color: toneConfig ? seckillPriceColor : colorStyle.theme,
                  }"
                >
                  ¥<span class="num">3200.00</span>
                </div>
                <div
                  class="yprice"
                  :class="checkboxInfo.indexOf(2) == -1 && !seckillConfig ? 'on' : ''"
                  v-if="checkboxInfo.indexOf(3) != -1"
                  :style="{
                    color: goodsPriceColor,
                  }"
                >
                  ¥3699.00
                </div>
                <div
                  class="bnt"
                  :class="checkboxInfo.indexOf(3) == -1 && !seckillConfig ? 'on2' : ''"
                  v-if="!seckillConfig"
                  :style="{
                    color: toneConfig ? goodsBntTxtColor : '#fff',
                    background: toneConfig
                      ? `linear-gradient(90deg,${goodsBntColorRight} 0%,${goodsBntColorLeft} 100%)`
                      : themeColor,
                  }"
                >
                  <div class="bntCon">
                    抢
                    <img src="@diy/assets/images/dian.png" />
                  </div>
                </div>
              </div>
            </div>
          </template>
        </div>
      </div>
    </common_wrapper>
  </div>
</template>

<script>
// 模块级具名导出：DIY 编辑器新增组件时经聚合层取默认配置工厂
// （原 Vue2 时代通过组件选项 data().defaultConfig 暴露；timestamp 依赖实例 props，故为工厂函数）
export const getDefaultConfig = (num) => ({
  cname: '秒杀',
  name: 'seckill',
  desc: '秒杀介绍',
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
  },
  shadowConfig: {
    title: '阴影设置',
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
    colorConfig: {
      title: '阴影颜色',
      default: [
        {
          item: 'rgba(0,0,0,0.1)',
        },
      ],
      color: [
        {
          item: 'rgba(0,0,0,0.1)',
        },
      ],
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
  imgBgConfig: {
    info: '建议：710px * 96px',
    url: Setting.apiBaseURL.replace(/adminapi/, '') + 'statics/images/seckillBg.png',
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
    url: __asset_seckill02_0,
    type: 'code',
    delType: 1,
    name: '标题图片',
  },
  imgColorConfig: {
    info: '建议：154px * 32px',
    url: __asset_seckill01_1,
    type: 'code',
    delType: 1,
    name: '标题图片',
  },
  titleTxtConfig: {
    title: '标题文字',
    value: '限时秒杀',
    place: '请输入标题文字',
    max: 6,
  },
  rightBntConfig: {
    title: '右侧按钮',
    value: '更多',
    place: '请输入右侧按钮',
    max: 6,
  },
  goodStyleConfig: {
    title: '选择风格',
    tabVal: 0,
    tabList: [
      {
        name: '单列展示',
      },
      {
        name: '两列纵向',
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
        name: '秒杀进度',
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
  seckillConfig: {
    title: '秒杀按钮',
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
  tipsColor: {
    title: '距离结束',
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
    title: '距离结束',
    name: 'tipsColor2',
    default: [
      {
        item: '#666',
      },
    ],
    color: [
      {
        item: '#666',
      },
    ],
  },
  numberBgColor: {
    title: '数字背景',
    name: 'numberBgColor',
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
  numberBgColor2: {
    title: '数字背景',
    name: 'numberBgColor2',
    default: [
      {
        item: '#E93323',
      },
      {
        item: '#E93323',
      },
    ],
    color: [
      {
        item: '#E93323',
      },
      {
        item: '#E93323',
      },
    ],
  },
  numberColor: {
    title: '数字',
    name: 'numberColor',
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
  numberColor2: {
    title: '数字',
    name: 'numberColor2',
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
  seckillPriceColor: {
    title: '秒杀价格',
    name: 'seckillPriceColor',
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
  seckillPriceColor2: {
    title: '秒杀价格',
    name: 'seckillPriceColor2',
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
  progressColor: {
    title: '进度条颜色',
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
  progressTxtColor: {
    title: '秒杀进度',
    name: 'progressTxtColor',
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
});
</script>

<script setup>
import { ref, watch, onMounted, nextTick } from 'vue';
import { useMobildConfigStore } from '@diy/store/mobildConfig';
import __asset_seckill02_0 from '@/assets/images/seckill02.png';
import __asset_seckill01_1 from '@/assets/images/seckill01.png';
import Setting from '@/setting';

defineOptions({
  name: 'home_seckill',
  cname: '秒杀',
  configName: 'c_home_seckill',
  icon: '#iconzujian-miaosha',
  type: 1,
  // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'seckill',
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
const numberBgColorLeft = ref('');
const numberBgColor = ref('');
const numberBgColorLeft2 = ref('');
const numberBgColor2 = ref('');
const numberColor = ref('');
const numberColor2 = ref('');
const rightBntTxt = ref('');
const headerBntColor = ref('');
const headerBntColor2 = ref('');
const bntNumber = ref(0);
const styleConfig = ref(0);
const headerBgColorLeft = ref('');
const headerBgColorRight = ref('');
const imgColorUrl = ref('');
const titleConfig = ref(0);
const titleTxtConfig = ref('');
const bgColor = ref('');
const bottomBgColor = ref('');
const paddingConfig = ref({
  title: '内边距',
  val: 0,
  isAll: false,
  valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
});
const marginConfig = ref({
  title: '外边距',
  val: 0,
  isAll: false,
  valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
});
const titleText = ref('');
const titleTabVal = ref(0);
const checkboxInfo = ref([]);
const imgRadius = ref(0);
const bgRadius = ref(0);
const bgRadius2 = ref(0);
const goodsName = ref('');
const goodsNameColor = ref('');
const goodsPriceColor = ref('');
const seckillPriceColor = ref('');
const seckillPriceColor2 = ref('');
const toneConfig = ref(0);
const goodsBntColorLeft = ref('');
const goodsBntColorRight = ref('');
const goodStyleConfig = ref(0);
const goodsBntTxtColor = ref('');
const seckillConfig = ref(0);
const progressColorLeft = ref('');
const progressColorRight = ref('');
const numberConfig = ref(1);
const titleColor = ref('');
const titleNumber = ref(0);
const progressTxtColor = ref('');
const themeColor = ref('');
const themeColor2 = ref('');

watch(
  pageData,
  (nVal) => {
    setConfig(nVal);
  },
  { deep: true },
);
watch(
  () => props.num,
  (nVal) => {
    let data = mobildConfigStore.defaultArray[nVal];
    setConfig(data);
  },
  { deep: true },
);
watch(
  () => mobildConfigStore.defaultArray,
  () => {
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
    if (data.prConfig) {
      data.paddingConfig.valList[1].val = data.prConfig.val;
      data.paddingConfig.valList[3].val = data.prConfig.val;
    }
    if (data.bottomConfig) data.paddingConfig.valList[2].val = data.bottomConfig.val;
  }
  if (isLegacyMargin) {
    if (data.mbConfig) data.marginConfig.valList[0].val = data.mbConfig.val;
  }

  if (data.mbConfig || data.marginConfig) {
    imgUrl.value = data.imgConfig.url;
    imgBgUrl.value = data.imgBgConfig.url;
    imgColorUrl.value = data.imgColorConfig.url;
    tipsColor.value = data.tipsColor.color[0].item;
    tipsColor2.value = data.tipsColor2.color[0].item;
    let numberBgColorRight = data.numberBgColor.color[1].item;
    numberBgColorLeft.value = data.numberBgColor.color[0].item;
    numberBgColor.value = `linear-gradient(90deg,${numberBgColorLeft.value} 0%,${numberBgColorRight} 100%)`;
    let numberBgColorRight2 = data.numberBgColor2.color[1].item;
    numberBgColorLeft2.value = data.numberBgColor2.color[0].item;
    numberBgColor2.value = `linear-gradient(90deg,${numberBgColorLeft2.value} 0%,${numberBgColorRight2} 100%)`;
    numberColor.value = data.numberColor.color[0].item;
    numberColor2.value = data.numberColor2.color[0].item;
    rightBntTxt.value = data.rightBntConfig.value;
    headerBntColor.value = data.headerBntColor.color[0].item;
    headerBntColor2.value = data.headerBntColor2.color[0].item;
    bntNumber.value = data.bntNumber.val;
    styleConfig.value = data.styleConfig.tabVal;
    headerBgColorLeft.value = data.headerBgColor.color[0].item;
    headerBgColorRight.value = data.headerBgColor.color[1].item;
    titleConfig.value = data.titleConfig.tabVal;
    titleTxtConfig.value = data.titleTxtConfig.value;
    let bgColorLeft = data.moduleColor.color[0].item;
    let bgColorRight = data.moduleColor.color[1].item;
    bgColor.value = `linear-gradient(90deg,${bgColorLeft} 0%,${bgColorRight} 100%)`;

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
    let fillet = data.fillet.type;
    let filletVal = data.fillet.val;
    let valList = data.fillet.valList;
    bgRadius.value = fillet
      ? valList[0].val + 'px ' + valList[1].val + 'px 0 0'
      : filletVal + 'px ' + filletVal + 'px 0 0';
    bgRadius2.value = fillet
      ? '0 0 ' + valList[3].val + 'px ' + valList[2].val + 'px'
      : '0 0 ' + filletVal + 'px ' + filletVal + 'px';
    let goodsTabVal = data.goodsName.tabVal;
    goodsName.value = data.goodsName.tabList[goodsTabVal].style;
    goodsNameColor.value = data.goodsNameColor.color[0].item;
    goodsPriceColor.value = data.goodsPriceColor.color[0].item;
    toneConfig.value = data.toneConfig.tabVal;
    seckillPriceColor.value = data.seckillPriceColor.color[0].item;
    seckillPriceColor2.value = data.seckillPriceColor2.color[0].item;
    goodsBntColorLeft.value = data.goodsBntColor.color[0].item;
    goodsBntColorRight.value = data.goodsBntColor.color[1].item;
    goodStyleConfig.value = data.goodStyleConfig.tabVal;
    goodsBntTxtColor.value = data.goodsBntTxtColor.color[0].item;
    seckillConfig.value = data.seckillConfig.tabVal;
    progressColorLeft.value = data.progressColor.color[0].item;
    progressColorRight.value = data.progressColor.color[1].item;
    numberConfig.value = data.numberConfig.val;
    titleColor.value = data.titleColor.color[0].item;
    titleNumber.value = data.titleNumber.val;
    progressTxtColor.value = data.progressTxtColor.color[0].item;
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
        // margin-right: 8px;
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
      .pictrue {
        width: 43px;
        height: 18px;
        img {
          width: 100%;
          height: 100%;
        }
      }
      .tips {
        font-size: 13px;
        color: #fff;
        font-weight: 400;
        margin-left: 6px;
      }
      .time {
        display: flex;
        align-items: center;
        margin-left: 4px;
        color: #ff4444;
        span {
          width: 18px;
          height: 18px;
          font-size: 11px;
          text-align: center;
          line-height: 18px;
          background: #fff;
          border-radius: 2px;
        }
        em {
          font-size: 11px;
          margin: 0 3px;
          font-style: initial;
          font-weight: bold;
          color: #fff;
        }
      }
    }
  }
  .list-wrapper {
    display: flex;
    justify-content: center;
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
      overflow: hidden;
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
        height: 20px;
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
        .name {
          flex: 1;
        }
        .label {
          width: 40px;
          height: 15px;
          border-radius: 3px;
          margin-right: 5px;
          .labelBg {
            width: 100%;
            height: 100%;
            text-align: center;
            line-height: 15px;
            background-color: rgba(255, 255, 255, 0.9);
            font-size: 11px;
            border-radius: 2px;
          }
        }
      }
      .price {
        font-weight: 600;
        font-size: 12px;
        &.on {
          margin-top: 8px;
        }
        .num {
          font-size: 16px;
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
        position: relative;
        .label {
          width: 42px;
          height: 15px;
          border-radius: 8px;
          font-size: 11px;
          text-align: center;
          line-height: 15px;
          left: 5px;
          top: 5px;
          position: absolute;
          .labelBg {
            background-color: rgba(255, 255, 255, 0.9);
          }
        }
      }
      .title {
        font-size: 13px;
        margin-top: 6px;
      }
      .price {
        font-size: 11px;
        height: 20px;
        &.on {
          margin-top: 1px;
        }
      }
      .yprice {
        &.on {
          margin-top: 1px;
        }
      }
      .bnt {
        width: 33px;
        height: 20px;
        line-height: 20px;
        border-radius: 0 11px 11px 0;
        &.on2 {
          bottom: 3px;
        }
        .bntCon {
          position: relative;
          img {
            width: 11px;
            height: 20px;
            display: block;
            position: absolute;
            top: 0;
            left: -4px;
          }
        }
      }
    }

    .itemOne {
      position: relative;

      & ~ .itemOne {
        margin-top: 15px;
      }
      .empty-box {
        width: 120px;
        height: 120px;
        margin-right: 12px;
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
          height: 78px;
          .label {
            width: 96px;
            font-size: 11px;
            border-radius: 3px;
            .labelBg {
              display: flex;
              background-color: rgba(255, 255, 255, 0.9);
              border-radius: 3px;
              .num {
                border-radius: 3px 0 0 3px;
                color: #fff;
                width: 37px;
                text-align: center;
                margin-right: 4px;
              }
            }
          }
        }
        .bottom {
          height: 42px;
        }
        .name {
          font-size: 14px;
          color: #333333;
          margin-bottom: 6px;
        }
        .progressBg {
          width: 120px;
          height: 12px;
          background: #fdf0ed;
          border-radius: 8px;
          position: relative;

          .progressBar {
            border-radius: 8px;
            overflow: hidden;
            background-color: rgba(255, 255, 255, 0.9);
          }

          .progressTxt {
            position: absolute;
            right: -50px;
            color: #e93323;
            font-size: 11px;
            top: -3px;
          }

          img {
            position: absolute;
            width: 17px;
            height: 17px;
            display: block;
            top: 50%;
            margin-top: -8.5px;
            left: 65px;
          }
          .progress {
            width: 75px;
            height: 12px;
            line-height: 12px;
            background: linear-gradient(45deg, #ff7931 0%, #e93323 100%);
            border-radius: 8px;
            font-size: 9px;
            color: #fff;
            padding-left: 7px;
          }
        }
        .price {
          font-size: 12px;
          color: #e93323;
          .label {
            font-weight: 600;
            margin-left: 4px;
          }
          .num {
            font-size: 16px;
            font-weight: 600;
          }
        }
        .yprice {
          color: #999999;
          font-size: 12px;
          text-decoration: line-through;
          .num {
            margin-left: 4px;
          }
        }
        .bnt {
          width: 60px;
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
          position: relative;
          img {
            width: 65px;
            height: 50px;
            display: block;
          }
          .label {
            width: 42px;
            height: 15px;
            border-radius: 8px;
            font-size: 11px;
            text-align: center;
            line-height: 15px;
            left: 5px;
            top: 5px;
            position: absolute;
            .labelBg {
              background-color: rgba(255, 255, 255, 0.9);
            }
          }
        }
      }
      .title {
        margin-top: 8px;
        font-size: 13px;
        color: #333;
      }
      .price {
        width: 75px;
        height: 22px;
        line-height: 22px;
        position: relative;
        border-radius: 0 4px 4px 0;
        margin-top: 1px;
        font-weight: 500;
        height: 20px;
        font-size: 15px;
        padding-left: 13px;

        span {
          font-size: 12px;
          margin-right: 2px;
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
