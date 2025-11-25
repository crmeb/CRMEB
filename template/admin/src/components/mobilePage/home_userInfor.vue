<template>
  <div
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
      v-if="styleConfig"
      class="userInfor acea-row row-between-wrapper"
      :style="{
        background: `linear-gradient(90deg,${bgColorLeft} 0%,${bgColorRight} 100%)`,
        borderRadius: bgRadius,
      }"
    >
      <div class="left acea-row row-middle">
        <div class="pictrue acea-row row-center-wrapper">
          <img :src="logoConfig" v-if="logoConfig" />
          <div class="empty-box" v-else>
            <img src="../../assets/images/shan.png" />
          </div>
        </div>
        <div class="text">
          <div class="name acea-row row-middle">用户名称<img src="../../assets/images/vip-diy.png" /></div>
          <div class="acea-row row-middle">
            <div
              class="progress"
              :style="{
                background: toneConfig ? progressBgColor : '#eee',
              }"
            >
              <div
                class="bgReds"
                :style="{
                  background: toneConfig
                    ? `linear-gradient(90deg,${progressLeft} 0%,${progressRight} 100%)`
                    : themeColor,
                }"
              ></div>
            </div>
            <div class="percent">3000/1000000</div>
          </div>
          <!--<div class="phone"><span class="iconfont iconshouji"></span>13000000000</div>-->
        </div>
      </div>
      <div class="right acea-row row-bottom">
        <div class="item" v-if="checkType.slice(0, 3).indexOf(1) != -1">
          <div class="num">20</div>
          <div>积分</div>
        </div>
        <div class="item" v-if="checkType.slice(0, 3).indexOf(2) != -1">
          <div class="num">200</div>
          <div>余额</div>
        </div>
        <div class="item" v-if="checkType.slice(0, 3).indexOf(0) != -1">
          <div class="num">2</div>
          <div>优惠券</div>
        </div>
        <div class="item" v-if="checkType.slice(0, 3).indexOf(4) != -1">
          <div class="num">80</div>
          <div>收藏</div>
        </div>
        <div class="item" v-if="checkType.slice(0, 3).indexOf(5) != -1">
          <div class="num">80</div>
          <div>浏览</div>
        </div>
      </div>
    </div>
    <div
      v-else
      class="userInfor"
      :style="{
        background: `linear-gradient(90deg,${bgColorLeft} 0%,${bgColorRight} 100%)`,
        borderRadius: bgRadius,
      }"
    >
      <div class="acea-row row-between-wrapper">
        <div class="left acea-row row-middle">
          <div class="pictrue acea-row row-center-wrapper">
            <img :src="logoConfig" v-if="logoConfig" />
            <div class="empty-box" v-else>
              <img src="../../assets/images/shan.png" />
            </div>
          </div>
          <div class="text">
            <div class="name acea-row row-middle">用户名称<img src="../../assets/images/vip-diy.png" /></div>
            <div class="acea-row row-middle">
              <div
                class="progress"
                :style="{
                  background: toneConfig ? progressBgColor : '#eee',
                }"
              >
                <div
                  class="bgReds"
                  :style="{
                    background: toneConfig
                      ? `linear-gradient(90deg,${progressLeft} 0%,${progressRight} 100%)`
                      : themeColor,
                  }"
                ></div>
              </div>
              <div class="percent">3000/1000000</div>
            </div>
            <!--<div class="phone"><span class="iconfont iconshouji"></span>13000000000</div>-->
          </div>
        </div>
        
      </div>
      <div class="list acea-row row-around">
        <div class="item" v-if="checkType.indexOf(1) != -1">
          <div>积分<span class="num">20000</span></div>
        </div>
        <div class="item" v-if="checkType.indexOf(2) != -1">
          <div>余额<span class="num">200</span></div>
        </div>
        <div class="item" v-if="checkType.indexOf(0) != -1">
          <div>优惠券<span class="num">2</span></div>
        </div>
        <div class="item" v-if="checkType.indexOf(4) != -1">
          <div>收藏<span class="num">80</span></div>
        </div>
        <div class="item" v-if="checkType.indexOf(5) != -1">
          <div>浏览<span class="num">80</span></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, mapMutations } from 'vuex';
// import theme from "@/mixins/theme";
export default {
  name: 'home_userInfor',
  cname: '用户信息',
  configName: 'c_userInfor',
  icon: '#iconzujian-yonghuxinxi',
  type: 0, // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'userInfor', // 外面匹配名称
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
        cname: '用户信息',
        name: 'userInfor',
        timestamp: this.num,
        isHide: false,
        setUp: {
          tabVal: 0,
        },
        titleLeft: '展示设置',
        titleImg: '默认头像',
        titleRight: '进度条',
        titleCurrency: '通用样式',
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
          ],
        },
        checkboxInfo: {
          title: '是否展示',
          name: 'checkboxInfo',
          userType: 1,
          type: [1, 2],
          list: [
            {
              id: 1,
              name: '积分',
            },
            {
              id: 2,
              name: '余额',
            },
            {
              id: 4,
              name: '收藏',
            },
            {
              id: 0,
              name: '优惠券',
            },
            {
              id: 5,
              name: '浏览',
            },
          ],
        },
        logoConfig: {
          info: '建议：图片尺寸90px * 90px',
          url: '',
          type: 'code',
          delType: 1,
          name: '上传图片',
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
        progressColor: {
          title: '进度条',
          default: [
            {
              item: '#e93323',
            },
            {
              item: '#ff8933',
            },
          ],
          color: [
            {
              item: '#e93323',
            },
            {
              item: '#ff8933',
            },
          ],
        },
        progressBgColor: {
          title: '进度条背景',
          default: [
            {
              item: '#EEEEEE',
            },
          ],
          color: [
            {
              item: '#EEEEEE',
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
              item: '#fff',
            },
          ],
          color: [
            {
              item: '#fff',
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
          val: 0,
          min: 0,
        },
        mbConfig: {
          title: '页面间距',
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
          val: 0,
          min: 0,
          valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
        },
      },
      pageData: {},
      styleConfig: 0,
      checkType: [],
      logoConfig: '',
      progressLeft: '',
      progressRight: '',
      progressBgColor: '',
      bgColorLeft: '',
      bgColorRight: '',
      bottomBgColor: '',
      topConfig: 0,
      bottomConfig: 0,
      prConfig: 0,
      mTop: '',
      bgRadius: 0,
      toneConfig: 0,
      themeColor: '',
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
        this.checkType = data.checkboxInfo.type;
        this.logoConfig = data.logoConfig.url;
        this.toneConfig = data.toneConfig.tabVal;
        this.progressLeft = data.progressColor.color[0].item;
        this.progressRight = data.progressColor.color[1].item;
        this.progressBgColor = data.progressBgColor.color[0].item;
        this.bgColorLeft = data.moduleColor.color[0].item;
        this.bgColorRight = data.moduleColor.color[1].item;
        this.themeColor = `linear-gradient(90deg,${this.colorStyle.theme} 0%,${this.colorStyle.gradient} 100%)`;
        this.bottomBgColor = data.bottomBgColor.color[0].item;
        this.topConfig = data.topConfig.val;
        this.bottomConfig = data.bottomConfig.val;
        this.prConfig = data.prConfig.val;
        this.mTop = data.mbConfig.val;
        let fillet = data.fillet.type;
        let filletVal = data.fillet.val;
        let valList = data.fillet.valList;
        this.bgRadius = fillet
          ? valList[0].val + 'px ' + valList[1].val + 'px ' + valList[3].val + 'px ' + valList[2].val + 'px'
          : filletVal + 'px';
      }
    },
  },
};
</script>

<style scoped lang="scss">
.userInfor {
  padding: 14px 10px;
  .list {
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid #eee;
    .item {
      font-size: 13px;
      color: #999;
      .num {
        font-size: 14px;
        font-weight: 600;
        color: #333333;
        margin-left: 4px;
      }
    }
  }
  .right {
    &.on {
      .item {
        color: #666666;
      }
    }
    .item {
      text-align: center;
      font-weight: 400;
      color: #999;
      font-size: 11px;
      margin-left: 16px;
      .num {
        font-size: 16px;
        margin-bottom: 2px;
        font-weight: 600;
        color: #333333;
      }
    }
    .iconfont {
      font-weight: 400;
      //color: #333333;
      font-size: 15px;
      margin-bottom: 2px;
    }
  }
  .left {
    .pictrue {
      width: 45px;
      height: 45px;
      border-radius: 50%;
      margin-right: 10px;
      img {
        border-radius: 50%;
        width: 100%;
        height: 100%;
        object-fit: cover;
      }
      .empty-box {
        border-radius: 50%;
        background-color: #f3f9ff;
        border: 1px solid #eeeeee;
        .iconfont-diy {
          font-size: 20px;
        }
        img {
          width: 26px;
          height: 20px;
        }
      }
    }
    .text {
      font-weight: 400;
      color: #333333;
      font-size: 16px;
      .name {
        img {
          width: 38px;
          height: 16px;
          margin-left: 5px;
        }
      }
      .progress {
        overflow: hidden;
        background-color: #eeeeee;
        width: 60px;
        height: 7px;
        border-radius: 3px;
        position: relative;
        .bgReds {
          width: 80%;
          height: 100%;
          transition: width 0.6s ease;
          border-radius: 3px;
        }
      }
      .percent {
        font-size: 10px;
        margin-left: 4px;
      }
      .phone {
        font-weight: 400;
        //color: #333;
        font-size: 10px;
        margin-top: 3px;
        .iconshouji {
          margin-right: 2px;
          font-size: 12px;
        }
      }
    }
  }
}
</style>
