<template>
  <div
    class="couponCon"
    :style="{
      background: bottomBgColor,
      marginTop: mTop + 'px',
      paddingTop: topConfig + 'px',
      paddingBottom: bottomConfig + 'px',
      paddingLeft: prConfig + 'px',
      paddingRight: prConfig + 'px',
    }"
  >
    <div
      class="coupon1 acea-row row-middle"
      :style="{
        background: moduleColor2,
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
          <div
            class="money"
            :style="{
              color: toneConfig ? couponMoneyColor : colorStyle.theme,
            }"
          >
            <div><span class="lable">¥</span>70</div>
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
          <img src="../../assets/images/newVip02.png" />
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
          <div class="type">品类券</div>
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
              color: toneConfig ? couponMoneyColor : colorStyle.theme,
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
        background: moduleColor2,
        borderRadius: bgRadius,
      }"
      v-else-if="styleConfig == 2"
    >
      <div class="list acea-row row-middle">
        <div
          class="itemCon"
          :style="{
            marginRight: spacingConfig + 'px',
            background: toneConfig ? couponMoneyColor : colorStyle.theme,
          }"
          v-for="(item, index) in numberConfig"
          :key="index"
        >
          <div
            class="item"
            :style="{
              borderColor: toneConfig ? couponMoneyColor : colorStyle.theme,
            }"
          >
            <div
              class="left"
              :style="{
                color: toneConfig ? couponMoneyColor : colorStyle.theme,
              }"
            >
              <div class="num"><span>￥</span>50</div>
              <div class="txt">满100元可用</div>
            </div>
            <div
              class="right"
              :style="{
                color: toneConfig ? couponMoneyColor : colorStyle.theme,
                borderLeftColor: toneConfig ? couponMoneyColor : colorStyle.theme,
              }"
            >
              <div class="rightCon">立即领取</div>
            </div>
            <div
              class="roll up-roll"
              :style="{
                background: moduleColor2,
                borderColor: toneConfig ? couponMoneyColor : colorStyle.theme,
              }"
            ></div>
            <div
              class="roll down-roll"
              :style="{
                background: moduleColor2,
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
        background: moduleColor2,
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
          <div
            class="item"
            :style="{
              marginRight: spacingConfig + 'px',
            }"
            v-for="(item, index) in numberConfig"
            :key="index"
            v-if="index < 4"
          >
            <div
              class="type"
              :style="{
                color: toneConfig ? couponMoneyColor : colorStyle.theme,
                background: toneConfig ? couponMoneyColor : colorStyle.theme,
              }"
            >
              <div class="typeCon">通用券</div>
            </div>
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
        </div>
        <div
          class="pocket"
          :style="{
            background: toneConfig ? `linear-gradient(0deg,${bntBgColorRight} 0%,${bntBgColorLeft} 100%)` : themeColor2,
          }"
        >
          <div class="tips">先领券 再购物</div>
          <div class="info">领券下单·享购物优惠</div>
          <div
            class="bnt"
            :style="{
              color: toneConfig ? couponMoneyColor : colorStyle.theme,
              background: toneConfig ? couponMoneyColor : colorStyle.theme,
            }"
          >
            <div class="bntCon">立即领取</div>
          </div>
        </div>
      </div>
    </div>
    <div
      class="coupon5 acea-row row-middle"
      :style="{
        background: moduleColor2,
        borderRadius: bgRadius,
      }"
      v-else
    >
      <div class="list acea-row row-middle">
        <div
          class="item acea-row row-middle"
          :style="{
            marginRight: spacingConfig + 'px',
            background: toneConfig ? `linear-gradient(0deg,${bntBgColorRight} 0%,${bntBgColorLeft} 100%)` : themeColor2,
          }"
          v-for="(item, index) in numberConfig"
          :key="index"
        >
          <div
            class="left"
            :style="{
              color: toneConfig ? couponMoneyColor : colorStyle.theme,
            }"
          >
            <div class="money"><span class="label">¥</span>50</div>
            <div class="tips">满5000可用</div>
          </div>
          <div class="right acea-row row-center">
            <div class="rightCon">领取</div>
          </div>
          <div
            class="roll"
            :style="{
              background: moduleColor2,
            }"
          ></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex';
// import theme from "@/mixins/theme";
export default {
  name: 'home_coupon',
  cname: '优惠券',
  configName: 'c_home_coupon',
  icon: '#iconzujian-youhuiquan',
  type: 1, // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'coupon', // 外面匹配名称
  props: {
    index: {
      type: null,
    },
    num: {
      type: null,
    },
    colorStyle: {
      type: null,
    },
  },
  computed: {
    ...mapState('mobildConfig', ['defaultArray']),
  },
  watch: {
    pageData: {
      handler(nVal, oVal) {
        this.setConfig(nVal);
      },
      deep: true,
    },
    num: {
      handler(nVal, oVal) {
        let data = this.$store.state.mobildConfig.defaultArray[nVal];
        this.setConfig(data);
      },
      deep: true,
    },
    defaultArray: {
      handler(nVal, oVal) {
        let data = this.$store.state.mobildConfig.defaultArray[this.num];
        this.setConfig(data);
      },
      deep: true,
    },
  },
  // mixins: [theme],
  data() {
    return {
      // 默认初始化数据禁止修改
      defaultConfig: {
        cname: '优惠券',
        name: 'coupon',
        timestamp: this.num,
        isHide: false,
        setUp: {
          tabVal: 0,
        },
        titleLeft: '展示设置',
        titleData: '优惠券数据',
        titleRight: '优惠券样式',
        titleCurrency: '通用样式',
        styleConfig: {
          title: '选择风格',
          tabVal: 0,
          type: 'coupon',
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
        moduleColor2: {
          title: '组件背景',
          default: [
            {
              item: '#ffffff',
            },
          ],
          color: [
            {
              item: '#ffffff',
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
      },
      pageData: {},
      numberConfig: 0,
      styleConfig: 0,
      toneConfig: 0,
      couponMoneyColor: '',
      bntBgColorLeft: '',
      bntBgColorRight: '',
      couponBgColor: '',
      spacingConfig: 0,
      moduleColor: '',
      moduleColor2: '',
      topConfig: 0,
      bottomConfig: 0,
      prConfig: 0,
      bgRadius: 0,
      mTop: 0,
      bottomBgColor: '',
      themeColor: '',
      themeColor2: '',
    };
  },
  mounted() {
    this.$nextTick(() => {
      this.pageData = this.$store.state.mobildConfig.defaultArray[this.num];
      this.setConfig(this.pageData);
    });
  },
  methods: {
    setConfig(data) {
      if (!data) return;
      if (data.mbConfig) {
        this.numberConfig = data.numberConfig.val;
        this.styleConfig = data.styleConfig.tabVal;
        this.toneConfig = data.toneConfig.tabVal;
        this.couponMoneyColor = data.couponMoneyColor.color[0].item;
        this.bntBgColorLeft = data.bntBgColor.color[0].item;
        this.bntBgColorRight = data.bntBgColor.color[1].item;
        this.couponBgColor = data.couponBgColor.color[0].item;
        this.spacingConfig = data.spacingConfig.val;
        let moduleColorLeft = data.moduleColor.color[0].item;
        let moduleColorRight = data.moduleColor.color[1].item;
        this.moduleColor = `linear-gradient(90deg,${moduleColorLeft} 0%,${moduleColorRight} 100%)`;
        this.moduleColor2 = data.moduleColor2.color[0].item;
        this.topConfig = data.topConfig.val;
        this.bottomConfig = data.bottomConfig.val;
        this.prConfig = data.prConfig.val;
        let fillet = data.fillet.type;
        let filletVal = data.fillet.val;
        let valList = data.fillet.valList;
        this.bgRadius = fillet
          ? valList[0].val + 'px ' + valList[1].val + 'px ' + valList[3].val + 'px ' + valList[2].val + 'px'
          : filletVal + 'px';
        this.mTop = data.mbConfig.val;
        this.bottomBgColor = data.bottomBgColor.color[0].item;
        this.themeColor = `linear-gradient(90deg,${this.colorStyle.theme} 0%,${this.colorStyle.gradient} 100%)`;
        this.themeColor2 = `linear-gradient(0deg,${this.colorStyle.theme} 0%,${this.colorStyle.gradient} 100%)`;
      }
    },
  },
};
</script>

<style scoped lang="scss">
.couponCon {
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
  height: 110px;
  background: #ffffff;
  border-radius: 8px;
  padding: 12px 10px;

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
  padding: 19px 0 12px 12px;
  overflow: hidden;
  .list {
    margin-top: 12px;
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
  height: 99px;
  background: #fff;
  overflow: hidden;
  padding-left: 12px;
  width: 100%;
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
