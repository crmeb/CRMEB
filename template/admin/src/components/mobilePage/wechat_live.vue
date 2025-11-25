<template>
  <div
    class="mobile-page"
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
      class="live-broadcast"
      :class="styleConfig == 2 ? 'on' : ''"
      :style="{
        background: bgColor,
        borderRadius: bgRadius,
      }"
    >
      <div class="live-wrapper-c" v-if="styleConfig == 0">
        <div
          class="live-item-a"
          :style="{
            marginBottom: liveConfig + 'px',
          }"
          v-for="(item, index) in live"
          :key="index"
        >
          <div class="img-box">
            <div
              class="empty-box"
              :style="{
                borderRadius: imgRadius,
              }"
            >
              <img src="../../assets/images/shan.png" />
            </div>
            <div class="label bgblue" v-if="item.type == 1">
              <span class="txt">预告</span>
              <span class="msg">08月08日 20:00</span>
            </div>
            <div class="label bggary" v-if="item.type == 0">回放</div>
            <div class="label bgred" v-if="item.type == 2"><span class="iconfont-diy iconzhibozhong"></span>直播中</div>
          </div>
          <div class="info">
            <div class="title" v-if="checkboxInfo.indexOf(0) != -1">这里是直播标题这里是直播标题这里是直播标题...</div>
            <div class="people" v-if="checkboxInfo.indexOf(1) != -1">
              <img src="@/assets/images/ren.png" alt="" />
              <span>主播：王小丫</span>
            </div>
          </div>
        </div>
      </div>
      <div class="live-wrapper-b" v-else-if="styleConfig == 2">
        <div
          class="live-item-b"
          :style="{
            marginRight: liveConfig + 'px',
          }"
          v-for="(item, index) in live"
          :key="index"
        >
          <div class="img-box">
            <div
              class="empty-box"
              :style="{
                borderRadius: imgRadius,
              }"
            >
              <img src="../../assets/images/shan.png" />
            </div>
            <div
              class="label bgblue"
              :style="{
                borderRadius: imgRadius2,
              }"
              v-if="item.type == 1"
            >
              <span
                class="txt"
                :style="{
                  borderRadius: imgRadius2,
                }"
                >预告</span
              >
              <span class="msg">08月08日 20:00</span>
            </div>
            <div
              class="label bggary"
              :style="{
                borderRadius: imgRadius2,
              }"
              v-if="item.type == 0"
            >
              回放
            </div>
            <div
              class="label bgred"
              :style="{
                borderRadius: imgRadius2,
              }"
              v-if="item.type == 2"
            >
              <span class="iconfont-diy iconzhibozhong"></span>直播中
            </div>
          </div>
          <div class="info">
            <div class="title line1" v-if="checkboxInfo.indexOf(0) != -1">直播标题直播标题直播标 题直播标题</div>
            <div class="people" v-if="checkboxInfo.indexOf(1) != -1">
              <img src="@/assets/images/ren.png" alt="" />
              <span>主播：王小丫</span>
            </div>
          </div>
        </div>
      </div>
      <div class="live-wrapper-a" v-else-if="styleConfig == 1">
        <div
          class="live-item-a"
          :style="{
            marginBottom: liveConfig + 'px',
          }"
          v-for="(item, index) in live"
          :key="index"
        >
          <div class="img-box">
            <div
              class="empty-box"
              :style="{
                borderRadius: imgRadius,
              }"
            >
              <img src="../../assets/images/shan.png" />
            </div>
            <div class="label bgblue" v-if="item.type == 1">
              <span class="txt">预告</span>
              <span class="msg">08月08日 20:00</span>
            </div>
            <div class="label bggary" v-if="item.type == 0">回放</div>
            <div class="label bgred" v-if="item.type == 2"><span class="iconfont-diy iconzhibozhong"></span>直播中</div>
          </div>
          <div class="info">
            <div class="left">
              <div class="title line2" v-if="checkboxInfo.indexOf(0) != -1">直播标题直播标题直播标 题直播标题</div>
            </div>
            <div class="goods-wrapper">
              <template v-if="item.goods.length > 0">
                <div class="goods-item" v-for="(goods, index) in item.goods" :key="index">
                  <img src="../../assets/images/shan.png" alt="" />
                  <span v-if="index < 2">￥{{ goods.price }}</span>
                  <span class="num" v-else>+5</span>
                </div>
              </template>
            </div>
          </div>
        </div>
      </div>
      <div class="live-wrapper-d" v-else>
        <div v-for="(item, index) in live" :key="index">
          <div class="live-item-d" v-if="index == 0">
            <div
              class="img-box"
              :style="{
                borderRadius: imgRadius,
              }"
            >
              <div class="empty-box">
                <img src="../../assets/images/shan.png" />
              </div>
              <div class="label bgred" v-if="item.type == 2">
                <span class="iconfont-diy iconzhibozhong"></span>直播中
              </div>
              <div
                class="info"
                v-if="checkboxInfo.indexOf(0) != -1 || checkboxInfo.indexOf(1) != -1"
                :style="{
                  borderRadius: imgRadius3,
                }"
              >
                <div class="title line1" v-if="checkboxInfo.indexOf(0) != -1">
                  这里是直播标题这里是直播标题这里是直播标题...
                </div>
                <div class="people" v-if="checkboxInfo.indexOf(1) != -1">
                  <img src="@/assets/images/ren.png" alt="" />
                  <span>主播：王小丫</span>
                </div>
              </div>
            </div>
          </div>
          <div
            class="live-item-d on"
            v-else
            :style="{
              marginTop: liveConfig + 'px',
            }"
          >
            <div class="name line1">
              <div class="label" v-if="item.type == 1">预告</div>
              <div class="label bggary" v-if="item.type == 0">回放</div>
              <div v-if="checkboxInfo.indexOf(0) != -1">这里是直播标题这里...</div>
            </div>
            <div class="people acea-row row-middle" v-if="checkboxInfo.indexOf(1) != -1">
              <img src="@/assets/images/ren.png" alt="" />
              <div class="acea-row row-middle">
                <span>主播：王小丫</span>
                <div class="line"></div>
                <span>08月28日 18:00</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, mapMutations } from 'vuex';
export default {
  name: 'wechat_live',
  cname: '小程序直播',
  configName: 'c_wechat_live',
  type: 1, // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'liveBroadcast', // 外面匹配名称
  icon: '#iconzujian-xiaochengxuzhibo',
  props: {
    index: {
      type: null,
      default: -1,
    },
    num: {
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
  data() {
    return {
      // 默认初始化数据禁止修改
      defaultConfig: {
        cname: '小程序直播',
        name: 'liveBroadcast',
        timestamp: this.num,
        isHide: false,
        setUp: {
          tabVal: 0,
        },
        titleLeft: '展示设置',
        titleContent: '内容展示',
        titleRight: '直播样式',
        titleCurrency: '通用样式',
        styleConfig: {
          title: '选择风格',
          tabVal: 0,
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
          ],
        },
        numberConfig: {
          title: '直播数量',
          val: 3,
          min: 1,
        },
        checkboxInfo: {
          title: '展示信息',
          name: 'checkboxInfo',
          type: [0, 1],
          list: [
            {
              id: 0,
              name: '直播标题',
            },
            {
              id: 1,
              name: '用户名称',
            },
          ],
        },
        liveConfig: {
          title: '直播间距',
          val: 10,
          min: 0,
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
          val: 8,
          min: 0,
          valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
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
      live: [
        {
          title: '直播中',
          name: 'playBg',
          type: 2,
          color: '',
          icon: 'iconzhibozhong',
          goods: [
            {
              img: '',
              price: '199',
            },
          ],
        },
        {
          title: '预告',
          name: 'notBg',
          type: 1,
          color: '',
          icon: 'iconweikaishi',
          goods: [
            {
              img: '',
              price: '199',
            },
            {
              img: '',
              price: '199',
            },
            {
              img: '',
              price: '199',
            },
          ],
        },
        {
          title: '回放',
          name: 'endBg',
          type: 0,
          color: '',
          icon: 'iconyijieshu',
          goods: [
            {
              img: '',
              price: '199',
            },
            {
              img: '',
              price: '199',
            },
          ],
        },
      ],
      confObj: {},
      pageData: {},
      styleConfig: 0,
      checkboxInfo: [],
      liveConfig: 0,
      imgRadius: 0,
      bgColor: '',
      bottomBgColor: '',
      mTop: 0,
      topConfig: 0,
      bottomConfig: 0,
      prConfig: 0,
      bgRadius: '',
      imgRadius2: 0,
      imgRadius3: 0,
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
        this.styleConfig = data.styleConfig.tabVal;
        this.checkboxInfo = data.checkboxInfo.type;
        this.liveConfig = data.liveConfig.val;
        let filletImg = data.filletImg.type;
        let filletValImg = data.filletImg.val;
        let valListImg = data.filletImg.valList;
        this.imgRadius = filletImg
          ? valListImg[0].val + 'px ' + valListImg[1].val + 'px ' + valListImg[3].val + 'px ' + valListImg[2].val + 'px'
          : filletValImg + 'px';
        this.imgRadius2 = filletImg ? valListImg[0].val + 'px 0 10px 0' : filletValImg + 'px 0 10px 0';
        this.imgRadius3 = filletImg
          ? '0 0 ' + valListImg[3].val + 'px ' + valListImg[2].val + 'px'
          : '0 0 ' + filletValImg + 'px ' + filletValImg + 'px';
        let bgColorLeft = data.moduleColor.color[0].item;
        let bgColorRight = data.moduleColor.color[1].item;
        this.bgColor = `linear-gradient(90deg,${bgColorLeft} 0%,${bgColorRight} 100%)`;
        this.bottomBgColor = data.bottomBgColor.color[0].item;
        this.mTop = data.mbConfig.val;
        this.topConfig = data.topConfig.val;
        this.bottomConfig = data.bottomConfig.val;
        this.prConfig = data.prConfig.val;
        let fillet = data.fillet.type;
        let filletVal = data.fillet.val;
        let valList = data.fillet.valList;
        this.bgRadius = fillet
          ? valList[0].val + 'px ' + valList[1].val + 'px ' + +valList[3].val + 'px ' + valList[2].val + 'px'
          : filletVal + 'px';
      }
    },
  },
};
</script>

<style scoped lang="scss">
.mobile-page {
  background: #f5f5f5;
  font-size: 12px;

  .live-broadcast {
    padding: 10px;
    background-color: #fff;

    &.on {
      padding-right: 0;
    }
  }
}

.live-wrapper-d {
  width: 100%;

  .live-item-d {
    .img-box {
      width: 100%;
      height: 335px;
      position: relative;
      background: #f3f9ff;

      .empty-box {
        width: 100%;
        height: 265px;
        background-color: unset;

        img {
          width: 88px;
          height: 68px;
        }
      }

      .label {
        top: 12px;
        left: 0;
        border-radius: 0 50px 50px 0;
        height: 20px;
      }

      .info {
        position: absolute;
        width: 100%;
        background-color: rgba(0, 0, 0, 0.2);
        left: 0;
        bottom: 0;
        padding: 12px;

        .title {
          color: #ffffff;
          font-size: 14px;
        }

        .people {
          display: flex;
          align-items: center;
          margin-top: 6px;

          img {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            margin-right: 5px;
          }

          span {
            font-size: 12px;
            color: #ffffff;
          }
        }
      }
    }

    &.on {
      width: 100%;
      border-radius: 8px;
      border: 1px solid #eee;
      padding: 10px;
      margin-top: 10px;

      .name {
        color: #333333;
        font-size: 14px;
        display: flex;
        align-items: center;

        .label {
          position: unset;
          width: 32px;
          height: 16px;
          text-align: center;
          line-height: 16px;
          background: linear-gradient(90deg, #208ff2 0%, #3fa6ff 98%);
          border-radius: 3px;
          margin-right: 8px;

          &.bggary {
            background: linear-gradient(90deg, #6d80ac 0%, #889ebd 100%);
          }
        }
      }

      .people {
        font-size: 12px;
        color: #999999;
        display: flex;
        align-items: center;
        margin-top: 6px;

        img {
          width: 20px;
          height: 20px;
          border-radius: 50%;
          margin-right: 6px;
        }

        .line {
          width: 1px;
          height: 11px;
          background: #eee;
          margin: 0 6px;
        }
      }
    }
  }
}

.live-wrapper-c {
  width: 100%;

  .live-item-a {
    position: relative;

    &:nth-last-child(1) {
      margin-bottom: 0 !important;
    }

    .empty-box {
      width: 100%;
      height: 200px;
      background: #f3f9ff;

      img {
        width: 88px;
        height: 68px;
      }
    }

    .img-box {
      .label {
        height: 20px;
        line-height: 20px;
        border-radius: 0 50px 50px 0;
        left: 0;
        top: 12px;

        &.bgred {
          width: 65px;
          background: linear-gradient(90deg, #ff2851 0%, #ff2851 100%);
        }

        &.bgblue {
          .txt {
            border-radius: 0 50px 50px 0;
            line-height: 20px;
          }

          .msg {
            height: 100%;
          }
        }

        &.bggary {
          width: 38px;
        }
      }
    }

    .info {
      .title {
        color: #333333;
        font-size: 14px;
        margin-top: 12px;
      }

      .people {
        display: flex;
        align-items: center;
        font-size: 12px;
        color: #999999;
        margin-top: 6px;

        img {
          width: 20px;
          height: 20px;
          background: #f3f9ff;
          border-radius: 50%;
          margin-right: 6px;
        }
      }
    }
  }
}

.live-wrapper-a {
  .live-item-a {
    display: flex;
    align-items: center;
    position: relative;
    margin-bottom: 10px;
    overflow: hidden;

    &:nth-last-child(1) {
      margin-bottom: 0 !important;
    }

    .img-box {
      position: relative;
      width: 166px;
      height: 118px;
      overflow: hidden;

      .empty-box {
        background-color: #f3f9ff;

        img {
          width: 65px;
          height: 50px;
        }
      }

      .bggary.label {
        width: 34px;
        line-height: 18px;
      }
    }

    .info {
      flex: 1;
      overflow: hidden;
      margin-left: 10px;

      .title {
        color: #333333;
        font-size: 14px;
      }
    }

    .goods-wrapper {
      margin-top: 8px;
      display: flex;

      .goods-item {
        position: relative;
        width: 48px;
        height: 48px;
        border-radius: 4px;
        margin-right: 8px;
        background: #f3f9ff;
        display: flex;
        align-items: center;
        justify-content: center;

        &:nth-child(3n) {
          margin-right: 0;
        }

        img {
          width: 36px;
          height: 28px;
        }

        span {
          position: absolute;
          left: 0;
          bottom: 0;
          color: #fff;
          font-size: 12px;
          width: 100%;
          height: 13px;
          background: rgba(0, 0, 0, 0.3);
          border-radius: 0 0 4px 4px;
          font-size: 11px;
          text-align: center;
          line-height: 14px;

          &.num {
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.35);
            font-size: 14px;
            color: #fefefe;
            border-radius: 4px;
            line-height: 48px;
          }
        }
      }
    }
  }
}

.live-wrapper-b {
  display: flex;
  overflow: hidden;

  .live-item-b {
    width: 140px;
    margin-right: 10px;

    &:nth-last-child(1) {
      margin-bottom: 0;
    }

    .img-box {
      position: relative;
      height: 100px;
      width: 140px;

      .empty-box {
        background: #f3f9ff;

        img {
          width: 65px;
          height: 50px;
        }
      }
    }

    .label {
      top: 0;
      left: 0;

      &.bgred {
        width: 61px;
      }

      &.bgblue {
        width: 128px;

        .txt {
          border-radius: 0;
        }
      }

      &.bggary {
        width: 34px;
      }
    }

    .info {
      width: 100%;

      .title {
        font-size: 14px;
        color: #333;
        margin-top: 6px;
      }

      .people {
        display: flex;
        align-items: center;
        margin-top: 6px;
        font-size: 12px;
        color: #999;

        img {
          width: 20px;
          height: 20px;
          margin-right: 5px;
          border-radius: 50%;
        }
      }
    }
  }
}

.bggary {
  background: linear-gradient(90deg, #6d80ac 0%, #889ebd 100%);
}

.bgred {
  background: linear-gradient(90deg, #ff2851 0%, #ff2851 100%);
}

.label {
  display: flex;
  align-items: center;
  justify-content: center;
  position: absolute;
  left: 6px;
  top: 6px;
  width: 61px;
  height: 18px;
  line-height: 18px;
  border-radius: 50px;
  color: #fff;
  font-size: 11px;

  .iconfont-diy {
    font-size: 12px;
    margin-right: 5px;
  }

  &.bgblue {
    justify-content: inherit;
    width: 128px;
    background: rgba(0, 0, 0, 0.2);
    overflow: hidden;

    .txt {
      width: 34px;
      height: 100%;
      text-align: center;
      margin-right: 7px;
      background: linear-gradient(90deg, #208ff2 0%, #3fa6ff 98%);
      border-radius: 50px;
      line-height: 18px;
    }
  }

  &.bggary {
    width: 54px;
  }
}
</style>
