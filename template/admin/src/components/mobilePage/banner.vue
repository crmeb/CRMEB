<template>
  <div class="mobile-page" :style="{ marginTop: `${mTOP}px` }">
    <div
      v-if="styleConfig == 0 || styleConfig == 2"
      :style="{
        paddingLeft: prConfig + 'px',
        paddingRight: prConfig + 'px',
        paddingTop: topConfig + 'px',
        paddingBottom: bottomConfig + 'px',
        backgroundColor: bgColor,
      }"
    >
      <div class="banner" v-if="styleConfig == 0">
        <img
          :src="imgSrc[0].img"
          alt=""
          v-if="imgSrc.length && imgSrc[0].img"
          :style="{
            borderRadius: filletImg
              ? valListImg[0].val +
                'px ' +
                valListImg[1].val +
                'px ' +
                valListImg[3].val +
                'px ' +
                valListImg[2].val +
                'px'
              : filletValImg + 'px',
          }"
        />
        <div
          class="empty-box"
          v-else
          :style="{
            borderRadius: filletImg
              ? valListImg[0].val +
                'px ' +
                valListImg[1].val +
                'px ' +
                valListImg[3].val +
                'px ' +
                valListImg[2].val +
                'px'
              : filletValImg + 'px',
          }"
        >
          <img class="shan" src="../../assets/images/shan.png" />
        </div>
      </div>
      <div class="banner" v-else>
        <div class="acea-row row-middle">
          <div
            class="empty-box style3"
            :style="{
              borderRadius: filletImg
                ? '0 ' + valListImg[1].val + 'px ' + valListImg[3].val + 'px ' + '0'
                : '0 ' + filletValImg + 'px ' + filletValImg + 'px ' + '0',
            }"
          >
            <img
              :src="imgSrc[1].img"
              alt=""
              v-if="imgSrc.length > 1 && imgSrc[1].img"
              :style="{
                borderRadius: filletImg
                  ? '0 ' + valListImg[1].val + 'px ' + valListImg[3].val + 'px ' + '0'
                  : '0 ' + filletValImg + 'px ' + filletValImg + 'px ' + '0',
              }"
            />
          </div>
          <div
            class="empty-box style3 on"
            :style="{
              marginLeft: imgConfig + 'px',
              marginRight: imgConfig + 'px',
              borderRadius: filletImg
                ? valListImg[0].val +
                  'px ' +
                  valListImg[1].val +
                  'px ' +
                  valListImg[3].val +
                  'px ' +
                  valListImg[2].val +
                  'px'
                : filletValImg + 'px',
            }"
          >
            <img
              :src="imgSrc[0].img"
              alt=""
              v-if="imgSrc.length && imgSrc[0].img"
              :style="{
                borderRadius: filletImg
                  ? valListImg[0].val +
                    'px ' +
                    valListImg[1].val +
                    'px ' +
                    valListImg[3].val +
                    'px ' +
                    valListImg[2].val +
                    'px'
                  : filletValImg + 'px',
              }"
            />
            <img class="shan" src="../../assets/images/shan.png" v-else />
          </div>
          <div
            class="empty-box style3"
            :style="{
              borderRadius: filletImg
                ? valListImg[1].val + 'px 0 0 ' + valListImg[3].val + 'px'
                : filletValImg + 'px 0 0 ' + filletValImg + 'px',
            }"
          >
            <img
              :src="imgSrc[2].img"
              alt=""
              v-if="imgSrc.length > 2 && imgSrc[2].img"
              :style="{
                borderRadius: filletImg
                  ? valListImg[1].val + 'px 0 0 ' + valListImg[3].val + 'px'
                  : filletValImg + 'px 0 0 ' + filletValImg + 'px',
              }"
            />
          </div>
        </div>
      </div>
    </div>
    <div v-else>
      <div
        class="bg"
        :style="{
          background: bgColor,
        }"
      ></div>
      <div
        class="banner"
        :class="styleConfig == 1 ? 'on' : ''"
        :style="{
          paddingLeft: prConfig + 'px',
          paddingRight: prConfig + 'px',
          paddingTop: topConfig + 'px',
          paddingBottom: bottomConfig + 'px',
        }"
      >
        <img
          :src="imgSrc[0].img"
          :style="{
            borderRadius: filletImg
              ? valListImg[0].val +
                'px ' +
                valListImg[1].val +
                'px ' +
                valListImg[3].val +
                'px ' +
                valListImg[2].val +
                'px'
              : filletValImg + 'px',
          }"
          alt=""
          v-if="imgSrc.length && imgSrc[0].img"
        />
        <div
          class="empty-box"
          :style="{
            borderRadius: filletImg
              ? valListImg[0].val +
                'px ' +
                valListImg[1].val +
                'px ' +
                valListImg[3].val +
                'px ' +
                valListImg[2].val +
                'px'
              : filletValImg + 'px',
          }"
          v-else
        >
          <img class="shan" src="../../assets/images/shan.png" />
        </div>
      </div>
    </div>
    <div>
      <!-- <div class="dot number acea-row " v-if="docStyle == 0">
		  <div class="num">2</div>
		  <div class="numCon">8</div>
	  </div> -->
      <div
        class="dot"
        v-if="docStyle == 2"
        :style="{
          paddingLeft: styleConfig == 2 ? prConfig + imgConfig * 2 + 26 + 'px' : prConfig + 13 + 'px',
          paddingRight: styleConfig == 2 ? prConfig + imgConfig * 2 + 26 + 'px' : prConfig + 13 + 'px',
          paddingBottom: bottomConfig + 10 + 'px',
          justifyContent: docPosition === 1 ? 'center' : docPosition === 2 ? 'flex-end' : 'flex-start',
        }"
      >
        <div
          class="line-dot"
          :style="{
            background: toneConfig ? dotBgColor : '#ddd',
          }"
        >
          <div
            class="item"
            :style="{
              background: toneConfig ? `${dotColor}` : `${colorStyle.theme}`,
            }"
          ></div>
        </div>
      </div>
      <div
        class="dot"
        :class="docStyle == 1 ? 'on' : docStyle == 3 ? 'on2' : ''"
        v-else
        :style="{
          paddingLeft: styleConfig == 2 ? prConfig + imgConfig * 2 + 26 + 'px' : prConfig + 10 + 'px',
          paddingRight: styleConfig == 2 ? prConfig + imgConfig * 2 + 26 + 'px' : prConfig + 10 + 'px',
          paddingBottom: bottomConfig + 10 + 'px',
          justifyContent: docPosition === 1 ? 'center' : docPosition === 2 ? 'flex-end' : 'flex-start',
        }"
      >
        <div
          class="dot-item"
          :class="docStyle == 1 ? 'ons' : ''"
          :style="{ background: toneConfig ? `${dotColor}` : `${colorStyle.theme}` }"
        ></div>
        <div
          class="dot-item"
          :style="{ background: toneConfig ? dotBgColor : '#ddd' }"
          v-for="(item, index) in 2"
          :key="index"
        ></div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex';
// import theme from "@/mixins/theme";
export default {
  name: 'banner', // 组件名称
  cname: '轮播图', // 标题名称
  icon: '#iconzujian-lunbotu',
  defaultName: 'swiperBg', // 外面匹配名称
  configName: 'c_banner', // 右侧配置名称
  type: 0, // 0 基础组件 1 营销组件 2工具组件
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
        cname: '轮播图',
        name: 'swiperBg',
        timestamp: this.num,
        isHide: false,
        setUp: {
          tabVal: 0,
        },
        titleLeft: '展示设置',
        titleContent: '内容设置',
        titleRight: '指示器设置',
        titleImg: '图片设置',
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
            {
              name: '样式三',
            },
          ],
        },
        // 图片列表
        swiperConfig: {
          maxList: 10,
          list: [
            {
              img: '',
              imgTitle: '图片',
              info: [
                {
                  title: '链接',
                  value: '',
                  tips: '请输入链接',
                  max: 100,
                },
              ],
            },
          ],
        },
        docConfig: {
          title: '指示器样式',
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
        docPosition: {
          title: '指示器位置',
          tabVal: 1,
          tabList: [
            {
              name: '左对齐',
            },
            {
              name: '居中对齐',
            },
            {
              name: '右对齐',
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
        dotColor: {
          title: '选中样式',
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
        dotBgColor: {
          title: '常规样式',
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
          val: 10,
          min: 0,
          valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
        },
        imgConfig: {
          title: '图片间距',
          val: 1,
          min: 0,
        },
        bgColor: {
          title: '底部背景',
          default: [
            {
              item: '#F62C2C',
            },
          ],
          color: [
            {
              item: '#F62C2C',
            },
          ],
        },
        topConfig: {
          title: '上边距',
          val: 10,
          min: 0,
        },
        bottomConfig: {
          title: '下边距',
          val: 10,
          min: 0,
        },
        // 左右间距
        prConfig: {
          title: '左右边距',
          val: 10,
          min: 0,
        },
        // 页面间距
        mbConfig: {
          title: '页面上间距',
          val: 0,
          min: 0,
        },
        txtStyle: {
          title: '指示器位置',
          type: 0,
          list: [
            {
              val: '居左',
              icon: 'icondoc_left',
            },
            {
              val: '居中',
              icon: 'icondoc_center',
            },
            {
              val: '居右',
              icon: 'icondoc_right',
            },
          ],
        },
      },
      styleConfig: 0,
      prConfig: 0,
      topConfig: 0,
      bottomConfig: 0,
      bgColor: '',
      filletImg: 0,
      filletValImg: 0,
      valListImg: [],
      mTOP: 0,
      docPosition: 0,
      toneConfig: 0,
      dotBgColor: '',
      dotColor: '',
      imgConfig: 0,
      pageData: {},
      edge: 0,
      imgSrc: [],
      docStyle: 0,
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
        this.styleConfig = data.styleConfig.tabVal || 0;
        this.prConfig = data.prConfig.val;
        this.topConfig = data.topConfig.val;
        this.bottomConfig = data.bottomConfig.val;
        this.bgColor = data.bgColor.color[0].item;
        this.filletImg = data.filletImg.type;
        this.filletValImg = data.filletImg.val;
        this.valListImg = data.filletImg.valList;
        this.mTOP = data.mbConfig.val;
        this.docPosition = data.docPosition.tabVal;
        this.toneConfig = data.toneConfig.tabVal;
        this.dotColor = data.dotColor.color[0].item;
        this.dotBgColor = data.dotBgColor.color[0].item;
        this.imgConfig = data.imgConfig.val;
        this.imgSrc = data.swiperConfig.list;
        this.docStyle = data.docConfig.tabVal;
      }
    },
  },
};
</script>

<style scoped lang="scss">
.empty-box {
  height: 170px;
  background-color: #f3f9ff;
  .shan {
    width: 65px !important;
    height: 50px !important;
  }
  &.style3 {
    width: 16px;
    height: 134px;
    border-radius: 0;
    img {
      width: 16px;
      height: 100%;
    }
  }
  &.on {
    flex: 1;
    height: 170px;
  }
}
.mobile-page {
  position: relative;
  width: auto;
  /*height: 140px;*/

  .banner {
    /*position: absolute;*/
    /*left: 0;*/
    /*top: 0;*/
    width: 100%;
    margin-top: 0;
    &.on {
      margin-top: -96px;
    }

    img {
      width: 100%;
      height: 100%;
      border-radius: 6px;
    }
  }

  .bg {
    width: 100%;
    height: 96px;
  }
}

.dot {
  position: absolute;
  left: 0;
  bottom: 0;
  width: 100%;
  display: flex;
  align-items: center;

  &.on {
    .dot-item {
      width: 5px;
      height: 5px;
      &.ons {
        width: 9px;
        height: 5px;
        border-radius: 4px;
      }
    }
  }

  &.on2 {
    .dot-item {
      width: 10px;
      height: 3px;
      border-radius: 4px;
    }
  }

  .dot-item {
    width: 6px;
    height: 6px;
    background: #dddddd;
    border-radius: 50%;
    margin: 0 3px;
  }

  .line-dot {
    width: 30px;
    height: 3px;
    border-radius: 4px;
    background-color: #dddddd;
    .item {
      width: 10px;
      height: 100%;
      border-radius: 4px;
      background-color: #e93323;
    }
  }

  &.number {
    width: 40px;
    height: 18px;
    border-radius: 100px;
    background: rgba(0, 0, 0, 0.3);
    color: #fff;
    font-size: 8px;
    .num {
      width: 22px;
      height: 100%;
      border-radius: 20px 0 20px 20px;
      background: rgba(0, 0, 0, 0.1);
      font-size: 10px;
      text-align: center;
      line-height: 18px;
    }
    .numCon {
      width: 18px;
      text-align: center;
      line-height: 18px;
    }
  }
}
</style>
