<template>
  <div
    class="mobile-page"
    :style="{
      background: bottomBgColor,
      marginTop: cSlider + 'px',
      paddingTop: topConfig + 'px',
      paddingBottom: bottomConfig + 'px',
      paddingLeft: prConfig + 'px',
      paddingRight: prConfig + 'px',
    }"
  >
    <div
      class="menusCon"
      :style="{
        background: `linear-gradient(90deg,${bgColorLeft} 0%,${bgColorRight} 100%)`,
        borderRadius: bgRadius,
      }"
    >
      <div class="menus">
        <div
          class="item on"
          v-if="styleConfig == 0"
          :style="{
            color: toneConfig ? textColor : '#333',
          }"
        >
          首页<span
            :style="{
              background: toneConfig
                ? `linear-gradient(90deg,${decorateColorLeft} 0%,${decorateColorRight} 100%)`
                : themeColor,
            }"
          ></span>
        </div>
        <div
          class="item on3"
          v-else-if="styleConfig == 1"
          :style="{
            color: toneConfig ? textColor2 : colorStyle.theme,
          }"
        >
          首页<span
            :style="{
              borderColor: toneConfig ? decorateColor : colorStyle.theme,
            }"
          ></span>
        </div>
        <div
          class="item on2"
          v-else
          :style="{
            color: toneConfig ? textColor3 : '#fff',
            background: toneConfig
              ? `linear-gradient(90deg,${decorateColorLeft} 0%,${decorateColorRight} 100%)`
              : themeColor,
          }"
        >
          首页
        </div>
        <div class="item" v-for="(item, index) in navList" :key="index" v-if="index < 20">
          {{ item.text.val }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, mapMutations } from 'vuex';
// import theme from "@/mixins/theme";
export default {
  name: 'nav_bar',
  configName: 'c_nav_bar',
  cname: '选项卡',
  icon: '#iconzujian-xuanxiangka1',
  type: 0, // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'tabNav', // 外面匹配名称
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
        cname: '选项卡',
        name: 'tabNav',
        timestamp: this.num,
        isHide: false,
        setUp: {
          tabVal: 0,
        },
        titleLeft: '展示设置',
        titleTab: '选项卡设置',
        titleRight: '选项卡样式',
        titleCurrency: '通用样式',
        styleConfig: {
          title: '选择风格',
          tabVal: 0,
          type: 'navBar',
        },
        stickyConfig: {
          title: '滑动置顶',
          tabVal: 0,
          tabList: [
            {
              name: '启用',
            },
            {
              name: '不启用',
            },
          ],
        },
        tabListConfig: {
          title: '鼠标拖拽版块可调整选项卡顺序',
          max: 100,
          list: [
            {
              text: {
                title: '显示文字',
                val: '首页',
                max: 6,
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
                max: 6,
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
                max: 6,
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
                max: 6,
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
      navList: [],
      toneConfig: 0,
      decorateColorLeft: '',
      decorateColorRight: '',
      decorateColor: '',
      bgColorLeft: '',
      bgColorRight: '',
      textColor: '',
      textColor2: '',
      textColor3: '',
      cSlider: 0,
      bgRadius: 0,
      bottomBgColor: '',
      topConfig: 0,
      bottomConfig: 0,
      prConfig: 0,
      styleConfig: 0,
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
        this.navList = data.tabListConfig.list;
        this.toneConfig = data.toneConfig.tabVal;
        this.decorateColorLeft = data.decorateColor.color[0].item;
        this.decorateColorRight = data.decorateColor.color[1].item;
        this.decorateColor = data.decorateColor2.color[0].item;
        this.textColor = data.textColor.color[0].item;
        this.textColor2 = data.textColor2.color[0].item;
        this.textColor3 = data.textColor3.color[0].item;
        this.bgColorLeft = data.moduleColor.color[0].item;
        this.bgColorRight = data.moduleColor.color[1].item;
        this.themeColor = `linear-gradient(90deg,${this.colorStyle.theme} 0%,${this.colorStyle.gradient} 100%)`;
        this.bottomBgColor = data.bottomBgColor.color[0].item;
        this.topConfig = data.topConfig.val;
        this.bottomConfig = data.bottomConfig.val;
        this.prConfig = data.prConfig.val;
        this.cSlider = data.mbConfig.val;
        this.styleConfig = data.styleConfig.tabVal;
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
.menusCon {
  overflow: hidden;
}
.menus {
  display: flex;
  align-items: center;
  width: 10000%;
  height: 40px;
  cursor: pointer;
  padding-left: 12px;

  .item {
    position: relative;
    color: #333;
    font-size: 14px;
    margin-right: 28px;
    z-index: 9;

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
      width: 46px;
      height: 24px;
      text-align: center;
      line-height: 24px;
      color: #fff;
      background: linear-gradient(90deg, #e93323 0%, #ff7931 100%);
      border-radius: 50px;
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
</style>
