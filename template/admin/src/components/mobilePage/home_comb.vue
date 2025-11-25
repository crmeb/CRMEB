<template>
  <div class="homeComb" :class="bannerImg ? '' : 'on'">
    <div class="bgImg">
      <img :src="bannerImg" v-if="bannerImg" />
    </div>
    <div class="bag-gradient" :style="[bgGradientStyle]"></div>
    <div class="searchBox acea-row row-between-wrapper">
      <div class="title" v-if="searchBox == 0">{{ titleConfig }}</div>
      <img :src="imgSrc" alt="" v-if="imgSrc && searchBox == 1" />
      <div class="box acea-row row-between-wrapper" :class="imgSrc ? '' : 'on'">
        <span v-if="hotWords" class="hot">{{ hotWords }}</span>
        <span v-else>{{ placeholders }}</span>
        <span class="iconfont iconsousuo1"></span>
      </div>
    </div>
    <div class="nav acea-row row-between-wrapper" v-if="classConfig == 0">
      <div class="list">
        <div class="listCon acea-row row-middle">
          <div
            class="item"
            :class="index == 0 ? 'on' : ''"
            :style="{
              marginLeft: contentConfig + 'px',
            }"
            v-for="(item, index) in navList"
            :key="index"
            v-if="index < 20"
          >
            {{ item.text.val }}
          </div>
        </div>
      </div>
      <div class="acea-row row-middle">
        <div class="bar"></div>
        <div class="iconfont iconerweima"></div>
      </div>
    </div>
    <div class="banner" :class="classConfig == 0 ? '' : 'on'" v-if="styleConfig == 0">
      <img
        :src="bannerImg"
        v-if="bannerImg"
        :style="{
          borderRadius: imgRadius,
        }"
      />
      <div
        class="empty-box"
        v-else
        :style="{
          borderRadius: imgRadius,
        }"
      >
        <img class="shan" src="../../assets/images/shan.png" />
      </div>
    </div>
    <div class="banner ons" :class="classConfig == 0 ? '' : 'on'" v-else>
      <div class="acea-row row-middle">
        <div
          class="empty-box style3"
          :style="{
            borderRadius: imgRadiusLeft,
          }"
        >
          <img
            :src="imgSrcList[1].img"
            alt=""
            v-if="imgSrcList.length > 1 && imgSrcList[1].img"
            :style="{
              borderRadius: imgRadiusLeft,
            }"
          />
        </div>
        <div
          class="empty-box style3 on"
          :style="{
            borderRadius: imgRadius,
          }"
        >
          <img
            :src="imgSrcList[0].img"
            alt=""
            v-if="imgSrcList.length && imgSrcList[0].img"
            :style="{
              borderRadius: imgRadius,
            }"
          />
          <img class="shan" src="../../assets/images/shan.png" v-else />
        </div>
        <div
          class="empty-box style3"
          :style="{
            borderRadius: imgRadiusRight,
          }"
        >
          <img
            :src="imgSrcList[2].img"
            alt=""
            v-if="imgSrcList.length > 2 && imgSrcList[2].img"
            :style="{
              borderRadius: imgRadiusRight,
            }"
          />
        </div>
      </div>
    </div>
    <div :class="styleConfig ? 'banDot' : ''">
      <div
        class="dot"
        v-if="docStyle == 2"
        :style="{
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
  name: 'home_comb', // 组件名称
  cname: '轮播搜索', // 标题名称
  icon: '#iconzujian-zuhezujian',
  defaultName: 'homeComb', // 外面匹配名称
  configName: 'c_home_comb', // 右侧配置名称
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
    bgGradientStyle() {
      return {
        'background-image': `linear-gradient(to bottom, rgba(245,245,245,0) 0%, rgba(245,245,245,0) 50%, ${this.gradientColor} 100%)`,
      };
    },
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
        const data = this.$store.state.mobildConfig.defaultArray[nVal];
        this.setConfig(data);
      },
      deep: true,
    },
    defaultArray: {
      handler(nVal, oVal) {
        const data = this.$store.state.mobildConfig.defaultArray[this.num];
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
        cname: '轮播搜索',
        name: 'homeComb',
        timestamp: this.num,
        isHide: false,
        setUp: {
          tabVal: 0,
        },
        titleLeft: '展示设置',
        titleSearch: '搜索设置',
        titleHotWords: '搜索热词',
        titleTab: '选项卡设置',
        titleImg: '图片设置',
        titleRight: '标签设置',
        titlePointer: '指示器设置',
        titleGradient: "渐变设置",

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
          ],
        },
        classConfig: {
          title: '分类设置',
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
        searchConfig: {
          title: '搜索设置',
          tabVal: 0,
          tabList: [
            {
              name: '正常显示',
            },
            {
              name: '滚动至顶部固定',
            },
          ],
        },
        searchBox: {
          title: '搜索框',
          tabVal: 1,
          tabList: [
            {
              name: '文字',
            },
            {
              name: 'logo',
            },
          ],
        },
        searchFix: {
          title: '定位类型',
          tabVal: 0,
          tabList: [
            {
              name: '门店',
            },
            {
              name: '用户定位',
            },
          ],
        },
        titleConfig: {
          title: '标题',
          value: '标题',
          place: '请输入标题',
          max: 6,
        },
        logoConfig: {
          info: '建议：200px * 100px',
          url: '',
          type: 'code',
          name: '默认logo',
        },
        logoUpConfig: {
          info: '建议：200px * 100px',
          url: '',
          type: 'code',
          name: '顶部固定logo',
        },
        inputConfig: {
          title: '提示文字',
          value: '请输入搜索词',
          place: '填写内容',
          max: 10,
        },
        hotWords: {
          list: [
            {
              val: '',
            },
          ],
        },
        gradientColor: {
          title: "组件背景",
          default: [
            {
              item: "#f5f5f5",
            },
          ],
          color: [
            {
              item: "#f5f5f5",
            },
          ],
        },
        numConfig: {
          placeholder: '设置搜索热词显示时间',
          title: '滚动时间',
          val: 3,
          type: 'words',
        },
        tabListConfig: {
          title: '鼠标拖拽版块可调整选项卡顺序',
          max: 100,
          list: [
            {
              text: {
                title: '显示文字',
                val: '首页',
                max: 4,
                pla: '请输入分类名称',
              },
              dataType: {
                title: '数据类型',
                tabVal: 0,
                tabList: [
                  {
                    name: '微页面',
                  },
                  {
                    name: '商品分类',
                  },
                ],
              },
              microPage: {
                name: '',
                id: 0,
              },
              classPage: {
                name: '',
                id: 0,
              },
            },
            {
              text: {
                title: '显示文字',
                val: '标题标题',
                max: 4,
                pla: '请输入分类名称',
              },
              dataType: {
                title: '数据类型',
                tabVal: 0,
                tabList: [
                  {
                    name: '微页面',
                  },
                  {
                    name: '商品分类',
                  },
                ],
              },
              microPage: {
                name: '',
                id: 0,
              },
              classPage: {
                name: '',
                id: 0,
              },
            },
            {
              text: {
                title: '显示文字',
                val: '标题标题',
                max: 4,
                pla: '请输入分类名称',
              },
              dataType: {
                title: '数据类型',
                tabVal: 0,
                tabList: [
                  {
                    name: '微页面',
                  },
                  {
                    name: '商品分类',
                  },
                ],
              },
              microPage: {
                name: '',
                id: 0,
              },
              classPage: {
                name: '',
                id: 0,
              },
            },
            {
              text: {
                title: '显示文字',
                val: '标题标题',
                max: 4,
                pla: '请输入分类名称',
              },
              dataType: {
                title: '数据类型',
                tabVal: 0,
                tabList: [
                  {
                    name: '微页面',
                  },
                  {
                    name: '商品分类',
                  },
                ],
              },
              microPage: {
                name: '',
                id: 0,
              },
              classPage: {
                name: '',
                id: 0,
              },
            },
          ],
        },
        contentConfig: {
          title: '内容间距',
          val: 20,
          min: 0,
        },
        classColor: {
          title: '下拉分类',
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
        swiperConfig: {
          title: '建议：图片尺寸702*320px；鼠标拖拽版块可调整图片顺序',
          bnt: '添加',
          maxList: 10,
          list: [
            {
              img: '',
              info: [
                {
                  title: '标题',
                  value: '标题',
                  tips: '选填，不超过4个字',
                  max: 4,
                },
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
      },
      pageData: {},
      imgSrc: '',
      bannerImg: [],
      navList: [],
      styleConfig: 0,
      classConfig: 0,
      searchConfig: 0,
      placeholders: '',
      hotWords: '',
      contentConfig: 0,
      docPosition: 0,
      toneConfig: 0,
      dotBgColor: '',
      dotColor: '',
      docStyle: 0,
      imgRadius: 0,
      imgRadiusLeft: 0,
      imgRadiusRight: 0,
      imgSrcList: [],
      searchBox: 0,
      searchFix: 0,
      titleConfig: '',
      gradientColor: '#f5f5f5'

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
      if (data.titleLeft) {
        this.navList = data.tabListConfig.list;
        this.styleConfig = data.styleConfig.tabVal;
        this.classConfig = data.classConfig.tabVal;
        this.searchConfig = data.searchConfig.tabVal;
        this.searchBox = data.searchBox.tabVal;
        this.searchFix = data.searchFix.tabVal;
        this.logoConfig = data.logoConfig.url;
        this.imgSrc = data.logoConfig.url;
        this.titleConfig = data.titleConfig.value;
        this.placeholders = data.inputConfig.value;
        this.hotWords = data.hotWords.list.length ? data.hotWords.list[0].val : '';
        this.contentConfig = data.contentConfig.val;
        this.imgSrcList = data.swiperConfig.list;
        this.bannerImg = data.swiperConfig.list.length ? data.swiperConfig.list[0].img : '';
        this.docPosition = data.docPosition.tabVal;
        this.toneConfig = data.toneConfig.tabVal;
        this.dotBgColor = data.dotBgColor.color[0].item;
        this.dotColor = data.dotColor.color[0].item;
        this.docStyle = data.docConfig.tabVal;
        this.gradientColor = data.gradientColor.color[0].item;
        let filletImg = data.filletImg.type;
        let filletValImg = data.filletImg.val;
        let valListImg = data.filletImg.valList;
        this.imgRadius = filletImg
          ? valListImg[0].val + 'px ' + valListImg[1].val + 'px ' + valListImg[3].val + 'px ' + valListImg[2].val + 'px'
          : filletValImg + 'px';
        this.imgRadiusLeft = filletImg
          ? '0 ' + valListImg[1].val + 'px ' + valListImg[3].val + 'px ' + '0'
          : '0 ' + filletValImg + 'px ' + filletValImg + 'px ' + '0';
        this.imgRadiusRight = filletImg
          ? valListImg[1].val + 'px 0 0 ' + valListImg[3].val + 'px'
          : filletValImg + 'px 0 0 ' + filletValImg + 'px';
      }
    },
  },
};
</script>

<style scoped lang="scss">
.empty-box {
  height: 160px;
  background-color: #f3f9ff;
  .shan {
    width: 65px !important;
    height: 50px !important;
  }
  &.style3 {
    width: 16px;
    border-radius: 0;
    height: 144px;
    img {
      width: 16px;
      height: 100%;
    }
  }
  &.on {
    flex: 1;
    margin: 0 12px;
    height: 160px;
  }
}
.banDot {
  .dot {
    padding: 0 40px;
  }
}
.dot {
  position: absolute;
  left: 0;
  bottom: 25px;
  width: 100%;
  display: flex;
  align-items: center;
  z-index: 9;
  padding: 0 30px;

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
.homeComb {
  width: 100%;
  position: relative;
  overflow: hidden;
  padding-bottom: 13px;
  .bag-gradient {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    z-index: 1;
  }
  &.on {
    background: rgba(0, 0, 0, 0.2);
  }
  .bgImg {
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0;
    z-index: 1;
    filter: blur(0);
    overflow: hidden;
    img {
      width: 100%;
      height: 100%;
      filter: blur(15px);
      transform: scale(1.5);
    }
  }
  .searchBox {
    position: relative;
    padding: 9px 12px 0 12px;
    z-index: 1;
    img {
      width: 69px;
      height: 30px;
      display: inline-block;
      margin-right: 10px;
    }
    .title {
      font-size: 14px;
      font-weight: 400;
      color: #fff;
      margin-right: 12px;
    }
    .map {
      font-size: 14px;
      font-weight: 400;
      color: #fff;
      margin-right: 12px;
      .iconfont {
        font-size: 12px;
      }
      .icondingwei {
        font-size: 14px;
        margin-right: 6px;
      }
    }
    .box {
      flex: 1;
      height: 29px;
      border-radius: 16px 16px 16px 16px;
      background-color: rgba(228, 228, 228, 0.4);
      padding: 0 12px;
      font-size: 12px;
      font-weight: 400;
      color: rgba(255, 255, 255, 0.5);
      &.on {
        width: 100%;
      }
      .hot {
        color: #fff;
      }
    }
  }
  .nav {
    position: relative;
    z-index: 1;
    padding: 0 12px;
    width: 100%;
    box-sizing: border-box;
    height: 42px;
    .list {
      width: 328px;
      overflow: hidden;
      .listCon {
        width: 10000%;
      }
    }
    .iconfont {
      font-size: 14px;
      color: #fff;
    }
    .bar {
      width: 1px;
      height: 15px;
      background: linear-gradient(135deg, rgba(215, 215, 215, 0) 0%, #fff 50%, rgba(215, 215, 215, 0) 100%);
      margin: 0 5px;
    }
    .item {
      font-weight: 400;
      color: #ffffff;
      font-size: 15px;
      position: relative;
      &.on {
        font-size: 16px;
        margin-left: 0 !important;
        .lines {
          position: absolute;
          width: 10px;
          height: 2px;
          background: #ffffff;
          transform: translateX(-50%);
          left: 50%;
          bottom: 0;
        }
      }
    }
  }
  .banner {
    width: 355px;
    height: 160px;
    position: relative;
    z-index: 1;
    border-radius: 6px;
    margin: 0 auto;
    &.on {
      margin-top: 15px;
    }
    &.ons {
      width: 100%;
    }
    img {
      width: 100%;
      height: 100%;
      border-radius: 6px;
    }
  }
}
</style>
