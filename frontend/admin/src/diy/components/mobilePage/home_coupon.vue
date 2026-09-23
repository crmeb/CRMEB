<template>
  <common_wrapper :config="configObj">
    <div
      class="couponCon"
      :style="{
        background: moduleColor,
        borderRadius: bgRadius,
      }"
    >
      <div
        class="coupon1 acea-row row-middle"
        :style="{
          borderRadius: bgRadius,
        }"
        v-if="styleConfig == 0"
      >
        <div class="list acea-row row-middle">
          <div
            class="item"
            v-for="(item, index) in numberConfig"
            :key="index"
            :style="{
              marginRight: spacingConfig + 'px',
              background: toneConfig ? couponBgColor : colorStyle.theme,
            }"
          >
            <div class="money">
              <div
                :style="{
                  color: toneConfig ? couponMoneyColor : colorStyle.theme,
                }"
              >
                <span class="lable">¥</span>70
              </div>
              <div class="tips">满5000可用</div>
            </div>
            <div
              class="sill"
              :style="{
                background: toneConfig
                  ? `linear-gradient(90deg,${bntBgColorRight} 0%,${bntBgColorLeft} 100%)`
                  : themeColor,
              }"
            >
              满200可用
            </div>
            <img src="@diy/assets/images/newVip02.png" />
          </div>
        </div>
      </div>
      <div
        class="coupon2 acea-row row-middle"
        :style="{
          background: moduleColor,
          borderRadius: bgRadius,
        }"
        v-else-if="styleConfig == 1"
      >
        <div class="list acea-row row-middle">
          <div
            class="item"
            :style="{
              marginRight: spacingConfig + 'px',
            }"
            v-for="(item, index) in numberConfig"
            :key="index"
          >
            <div
              class="type"
              :style="{
                color: toneConfig ? couponTypeColor : '#333333',
              }"
            >
              品类券
            </div>
            <div
              class="money"
              :style="{
                color: toneConfig ? couponMoneyColor : colorStyle.theme,
              }"
            >
              <span class="label">¥</span>50
            </div>
            <div
              class="tips"
              :style="{
                color: toneConfig ? couponReduceColor : colorStyle.theme,
              }"
            >
              满500元可用
            </div>
            <div
              class="bnt"
              :style="{
                background: toneConfig
                  ? `linear-gradient(90deg,${bntBgColorRight} 0%,${bntBgColorLeft} 100%)`
                  : themeColor,
                color: toneConfig ? bntTxtColor : '#ffffff',
              }"
            >
              去领取
            </div>
          </div>
        </div>
      </div>
      <div
        class="coupon acea-row row-middle"
        :style="{
          borderRadius: bgRadius,
        }"
        v-else-if="styleConfig == 2"
      >
        <div class="list acea-row row-middle">
          <div
            class="itemCon"
            :style="{
              marginRight: spacingConfig + 'px',
            }"
            v-for="(item, index) in numberConfig"
            :key="index"
          >
            <div
              class="item"
              :style="{
                color: toneConfig ? couponMoneyColor : colorStyle.theme,
                borderColor: toneConfig ? couponMoneyColor : colorStyle.theme,
              }"
            >
              <div class="left">
                <div
                  class="num"
                  :style="{
                    color: toneConfig ? couponMoneyColor : colorStyle.theme,
                  }"
                >
                  <span>￥</span>50
                </div>
                <div
                  class="txt"
                  :style="{
                    color: toneConfig ? couponReduceColor : colorStyle.theme,
                  }"
                >
                  满100元可用
                </div>
              </div>
              <div
                class="right"
                :style="{
                  color: toneConfig ? bntTxtColor : colorStyle.theme,
                  borderColor: toneConfig ? bntTxtColor : colorStyle.theme,
                  background: toneConfig
                    ? `linear-gradient(0deg,${bntBgColorRight} 0%,${bntBgColorLeft} 100%)`
                    : 'transparent',
                }"
              >
                <div class="rightCon">立即领取</div>
              </div>
              <div
                class="roll up-roll"
                :style="{
                  color: toneConfig ? couponMoneyColor : colorStyle.theme,
                  borderColor: toneConfig ? couponMoneyColor : colorStyle.theme,
                }"
              ></div>
              <div
                class="roll down-roll"
                :style="{
                  color: toneConfig ? couponMoneyColor : colorStyle.theme,
                  borderColor: toneConfig ? couponMoneyColor : colorStyle.theme,
                }"
              ></div>
            </div>
          </div>
        </div>
      </div>
      <div
        class="coupon4"
        :style="{
          borderRadius: bgRadius,
        }"
        v-else-if="styleConfig == 3"
      >
        <div
          class="list acea-row row-middle"
          :style="{
            background: toneConfig ? couponBgColor : colorStyle.theme,
          }"
        >
          <div class="listCon acea-row row-middle">
            <template v-for="(item, index) in numberConfig" :key="index">
              <div
                class="item"
                :style="{
                  marginRight: spacingConfig + 'px',
                }"
                v-if="index < 4"
              >
                <div
                  class="type"
                  :style="{
                    background: toneConfig ? couponTypeColor : colorStyle.theme,
                  }"
                >
                  <div
                    class="typeCon"
                    :style="{
                      color: toneConfig ? couponTypeColor : colorStyle.theme,
                    }"
                  >
                    通用券
                  </div>
                </div>
                <div
                  class="money"
                  :style="{
                    color: toneConfig ? couponMoneyColor : colorStyle.theme,
                  }"
                >
                  <span class="label">¥</span>50
                </div>
                <div
                  class="tips"
                  :style="{
                    color: toneConfig ? couponReduceColor : '#333333',
                  }"
                >
                  满5000可用
                </div>
              </div>
            </template>
          </div>
          <div
            class="pocket"
            :style="{
              background: toneConfig
                ? `linear-gradient(0deg,${bntBgColorRight} 0%,${bntBgColorLeft} 100%)`
                : themeColor2,
            }"
          >
            <div class="tips">先领券 再购物</div>
            <div class="info">领券下单·享购物优惠</div>
            <div
              class="bnt"
              :style="{
                background: toneConfig
                  ? `linear-gradient(90deg,${bntBgColorRight} 0%,${bntBgColorLeft} 100%)`
                  : colorStyle.theme,
              }"
            >
              <div
                class="bntCon"
                :style="{
                  background: toneConfig
                    ? `linear-gradient(90deg,${bntBgColorRight} 0%,${bntBgColorLeft} 100%)`
                    : 'rgba(255, 255, 255, 0.9)',
                  color: toneConfig ? bntTxtColor : colorStyle.theme,
                }"
              >
                立即领取
              </div>
            </div>
          </div>
        </div>
      </div>
      <div
        class="coupon5 acea-row row-middle"
        :style="{
          borderRadius: bgRadius,
        }"
        v-else
      >
        <div class="list acea-row row-middle">
          <div
            class="item acea-row row-middle"
            :style="{
              marginRight: spacingConfig + 'px',
              background: toneConfig
                ? `linear-gradient(0deg,${bntBgColorRight} 0%,${bntBgColorLeft} 100%)`
                : themeColor2,
            }"
            v-for="(item, index) in numberConfig"
            :key="index"
          >
            <div class="left">
              <div
                class="money"
                :style="{
                  color: toneConfig ? couponMoneyColor : colorStyle.theme,
                }"
              >
                <span class="label">¥</span>50
              </div>
              <div class="tips">满5000可用</div>
            </div>
            <div class="right acea-row row-center">
              <div class="rightCon">领取</div>
            </div>
            <div class="roll"></div>
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
  cname: '优惠券',
  name: 'coupon',
  desc: '优惠券的介绍',
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
  titleLeft: '展示设置',
  titleData: '优惠券数据',
  titleRight: '优惠券样式',
  titleCurrency: '通用样式',
  styleConfig: {
    title: '选择风格',
    tabVal: 0,
    tabList: [
      {
        name: '风格一',
      },
      {
        name: '风格二',
      },
      {
        name: '风格三',
      },
      {
        name: '风格四',
      },
    ],
  },
  numberConfig: {
    title: '展示数量',
    val: 5,
    min: 1,
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
  couponMoneyColor: {
    title: '优惠金额',
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
  couponReduceColor: {
    title: '满减文字',
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
  couponTypeColor: {
    title: '优惠券类型',
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
  bntTxtColor: {
    title: '按钮文字',
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
  bntBgColor: {
    title: '按钮背景',
    name: 'bntBgColor',
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
  couponBgColor: {
    title: '优惠券背景',
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
  spacingConfig: {
    title: '优惠券间距',
    val: 6,
    min: 0,
  },
  moduleColor: {
    title: '组件背景',
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
    val: 10,
    min: 0,
    valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
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

defineOptions({
  name: 'home_coupon',
  cname: '优惠券',
  configName: 'c_home_coupon',
  icon: '#iconzujian-youhuiquan',
  type: 1,
  // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'coupon',
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

const pageData = ref({});
const numberConfig = ref(0);
const styleConfig = ref(0);
const toneConfig = ref(0);
const couponBgColor = ref('');
const couponMoneyColor = ref('');
const couponReduceColor = ref('');
const couponTypeColor = ref('');
const bntTxtColor = ref('');
const bntBgColorLeft = ref('');
const bntBgColorRight = ref('');
const moduleColor = ref('');
const configObj = ref(null);
const bgRadius = ref(0);
const spacingConfig = ref(0);
const themeColor = ref('');
const themeColor2 = ref('');

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
      data[key] = JSON.parse(JSON.stringify(defaultConfig[key]));
    }
  }
  numberConfig.value = data.numberConfig.val;
  styleConfig.value = data.styleConfig.tabVal;
  toneConfig.value = data.toneConfig.tabVal;
  couponMoneyColor.value = data.couponMoneyColor.color[0].item;
  couponReduceColor.value = data.couponReduceColor.color[0].item;
  couponTypeColor.value = data.couponTypeColor.color[0].item;
  bntTxtColor.value = data.bntTxtColor.color[0].item;
  bntBgColorLeft.value = data.bntBgColor.color[0].item;
  bntBgColorRight.value = data.bntBgColor.color[1].item;
  couponBgColor.value = data.couponBgColor.color[0].item;
  spacingConfig.value = data.spacingConfig.val;
  let moduleColorLeft = data.moduleColor.color[0].item;
  let moduleColorRight = data.moduleColor.color[1].item;
  moduleColor.value = `linear-gradient(90deg,${moduleColorLeft} 0%,${moduleColorRight} 100%)`;
  let fillet = data.fillet.type;
  let filletVal = data.fillet.val;
  let valList = data.fillet.valList;
  bgRadius.value = fillet
    ? valList[0].val + 'px ' + valList[1].val + 'px ' + valList[3].val + 'px ' + valList[2].val + 'px'
    : filletVal + 'px';
  configObj.value = data;
  themeColor.value = `linear-gradient(90deg,${props.colorStyle.theme} 0%,${props.colorStyle.gradient} 100%)`;
  themeColor2.value = `linear-gradient(0deg,${props.colorStyle.theme} 0%,${props.colorStyle.gradient} 100%)`;
}
</script>

<style scoped lang="scss">
.couponCon {
  // display: inline-block;
  width: -webkit-fill-available;
  overflow: hidden;
}
.coupon5 {
  height: 78px;
  background: #ffffff;
  border-radius: 8px;
  padding-left: 10px;
  width: 100%;
  overflow: hidden;
  .list {
    flex-wrap: nowrap;
    .item {
      width: 114px;
      height: 54px;
      background: linear-gradient(0deg, #e93323 0%, #ff7931 100%);
      border-radius: 6px;
      position: relative;
      margin-right: 6px;

      .roll {
        width: 8px;
        height: 8px;
        background: #fff;
        border-radius: 50%;
        position: absolute;
        left: -4px;
      }
      .right {
        flex: 1;
        .rightCon {
          width: 20px;
          font-size: 12px;
          color: #fff;
          text-align: center;
          line-height: 1.2;
        }
      }
      .left {
        width: 86px;
        height: 100%;
        background: linear-gradient(0deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.8) 100%);
        border-radius: 6px;
        text-align: center;
        color: #e93323;
        padding: 8px 0;
        .money {
          font-size: 21px;
          line-height: 21px;
          font-family: D-DIN-PRO, D-DIN-PRO;
          font-weight: 600;

          .label {
            font-size: 14px;
          }
        }
        .tips {
          font-size: 11px;
        }
      }
    }
  }
}
.coupon4 {
  width: 100%;
  border-radius: 8px;

  .list {
    background: #e93323;
    border-radius: 12px;
    height: 86px;
    padding-left: 10px;
    position: relative;
    overflow: hidden;
    width: 100%;
    .listCon {
      flex-wrap: nowrap;
    }
    .pocket {
      text-align: center;
      background: linear-gradient(0deg, #e93323 0%, #ff7931 100%);
      position: absolute;
      top: 0;
      right: 0;
      width: 148px;
      height: 100%;
      padding-top: 10px;

      .tips {
        color: #ffffff;
        font-size: 16px;
      }
      .info {
        font-size: 10px;
        color: #f5f5f5;
      }
      .bnt {
        width: 80px;
        height: 24px;
        border-radius: 13px;
        background: #e93323;
        margin: 4px auto 0 auto;
        color: #e93323;
        .bntCon {
          width: 100%;
          height: 100%;
          background: rgba(255, 255, 255, 0.9);
          text-align: center;
          line-height: 24px;
          font-size: 12px;
          border-radius: 13px;
        }
      }
    }
    .item {
      width: 70px;
      height: 66px;
      background: #ffffff;
      border-radius: 6px;
      text-align: center;
      margin-right: 6px;
      .type {
        font-size: 11px;
        color: #e93323;
        width: 54px;
        height: 19px;
        background: #e93323;
        border-radius: 0 0 12px 12px;
        margin: 0 auto;

        .typeCon {
          background: rgba(255, 255, 255, 0.9);
          width: 100%;
          height: 100%;
          text-align: center;
          line-height: 19px;
        }
      }
      .money {
        font-size: 20px;
        font-family: D-DIN-PRO, D-DIN-PRO;
        font-weight: 600;
        margin-top: 4px;
        line-height: 24px;
        color: #e93323;
        .label {
          font-size: 14px;
        }
      }
      .tips {
        font-size: 9px;
        color: #333333;
      }
    }
  }
}
.coupon2 {
  background: linear-gradient(90deg, #e93323 0%, #ff7931 100%);
  height: 140px;
  padding-left: 12px;
  width: 100%;
  overflow: hidden;
  .list {
    flex-wrap: nowrap;
    .item {
      width: 102px;
      height: 116px;
      background: #ffffff;
      border-radius: 8px;
      text-align: center;
      padding-top: 14px;
      margin-right: 6px;
      .type {
        font-weight: 500;
        color: #333333;
        font-size: 12px;
      }
      .money {
        font-size: 22px;
        font-family: D-DIN-PRO, D-DIN-PRO;
        font-weight: 600;
        color: #e93323;
        line-height: 1.3;
        .label {
          font-size: 14px;
        }
      }
      .tips {
        font-size: 9px;
        color: #e93323;
      }
      .bnt {
        width: 68px;
        height: 24px;
        background: linear-gradient(90deg, #ff7931 0%, #e93323 100%);
        border-radius: 13px;
        color: #ffffff;
        font-size: 12px;
        text-align: center;
        line-height: 24px;
        margin: 6px auto 0 auto;
      }
    }
  }
}
.coupon1 {
  overflow: hidden;
  padding: 6px;
  .list {
    margin-top: 18px;
    display: inline-flex;
    flex-wrap: nowrap;

    .item {
      width: 78px;
      height: 76px;
      background: #f12a13;
      position: relative;
      border-radius: 6px 6px 15px 15px;
      margin-right: 6px;
      .money {
        width: 72px;
        height: 53px;
        background: #ffffff;
        border: 1px solid #fceae9;
        position: absolute;
        left: 3px;
        top: -18px;
        text-align: center;
        font-size: 20px;
        font-family: D-DIN-PRO, D-DIN-PRO;
        font-weight: 600;
        color: #e93323;
        padding-top: 6px;
        border-radius: 6px 6px 0 0;
        .lable {
          font-size: 14px;
        }
        .tips {
          font-size: 9px;
          color: #999999;
          font-weight: 400;
        }
      }
      .sill {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 39px;
        background: linear-gradient(90deg, #e93323 0%, #ff7931 100%);
        color: #fff;
        line-height: 46px;
        border-radius: 0 0 15px 15px;
        font-size: 10px;
        text-align: center;
      }
      img {
        position: absolute;
        left: 0;
        width: 78px;
        height: 10px;
        bottom: 32px;
      }
    }
  }
}
.coupon {
  overflow: hidden;
  width: 100%;
  padding: 12px;
  .list {
    flex-wrap: nowrap;
  }
  .itemCon {
    background: #e93323;
    border-radius: 8px;
    width: 137px;
    height: 75px;
    overflow: hidden;
    margin-right: 6px;
  }
  .item {
    flex-shrink: 0;
    position: relative;
    display: flex;
    width: 137px;
    height: 75px;
    background: rgba(255, 255, 255, 0.9);
    border-radius: 8px;
    color: #fff;
    margin-right: 10px;
    border: 1px solid #e93323;
    .left {
      width: 100px;
      height: 75px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      color: #e93323;
      padding-bottom: 5px;
      .num {
        font-size: 22px;
        font-weight: 600;

        span {
          font-size: 14px;
        }
      }
      .txt {
        font-size: 12px;
      }
    }
    .right {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 12px;
      border-left: 1px dashed #e93323;
      color: #e93323;
      .rightCon {
        width: 21px;
        text-align: center;
        line-height: 1.2;
      }
    }
    .roll {
      position: absolute;
      width: 8px;
      height: 8px;
      border-radius: 0 0 50% 50%;
      background: #fff;
      border: 1px solid #e93323;
      &.up-roll {
        right: 31px;
        top: -5px;
      }
      &.down-roll {
        right: 31px;
        bottom: -5px;
        border-radius: 50% 50% 0 0;
      }
    }
  }
}
</style>
