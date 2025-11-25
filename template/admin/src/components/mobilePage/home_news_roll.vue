<template>
  <div
    class="news-box"
    :style="{
      background: bottomBgColor,
      marginTop: slider + 'px',
      paddingTop: topConfig + 'px',
      paddingBottom: bottomConfig + 'px',
      paddingLeft: prConfig + 'px',
      paddingRight: prConfig + 'px',
    }"
    v-if="list.length"
  >
    <div
      class="item"
      :style="{
        background: `linear-gradient(90deg,${bgColorLeft} 0%,${bgColorRight} 100%)`,
        borderRadius: bgRadius,
      }"
      v-if="styleConfig == 0"
    >
      <div class="img-box" v-if="titleConfig == 0"><img :src="imgUrl" alt="" /></div>
      <div
        class="top"
        v-else
        :style="{
          color: toneConfig ? titleColor : '#fff',
          background: toneConfig
            ? `linear-gradient(90deg,${titleBgColorLeft} 0%,${titleBgColorRight} 100%)`
            : colorStyle.theme,
        }"
      >
        {{ titleTxtConfig || '商城头条' }}
      </div>
      <div
        class="right-box"
        :style="{
          color: newsColor,
        }"
      >
        {{ list[0].chiild[0].val }}
      </div>
      <span
        class="iconfont iconjinru"
        :style="{
          color: bntColor,
        }"
        v-if="!buttonConfig"
      ></span>
    </div>
    <div
      class="list"
      v-else
      :style="{
        background: `linear-gradient(90deg,${bgColorLeft} 0%,${bgColorRight} 100%)`,
        borderRadius: bgRadius,
      }"
    >
      <div class="title acea-row row-between-wrapper">
        <div class="pictrue" v-if="titleConfig == 0">
          <img :src="imgUrl" alt="" />
        </div>
        <div
        class="top"
        v-else
        :style="{
          color: !toneConfig ? titleColor : colorStyle.theme,
        }"
        >
          {{ titleTxtConfig || '商城头条' }}
        </div>
        <div
          v-if="!buttonConfig"
          :style="{
            color: bntColor,
          }"
        >
          {{ textConfig }}<span class="iconfont iconjinru"></span>
        </div>
      </div>
      <div
        class="text line1"
        v-for="(item, index) in list"
        :key="index"
        :style="{
          color: newsColor,
        }"
      >
        <span class="num" :class="index == 0 ? 'on' : index == 1 ? 'on2' : index == 2 ? 'on3' : ''">{{
          index + 1
        }}</span
        >{{ item.chiild[0].val }}
      </div>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex';
// import theme from "@/mixins/theme";
export default {
  name: 'home_news_roll',
  cname: '新闻公告',
  configName: 'c_news_roll',
  type: 0, // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'news', // 外面匹配名称
  icon: '#iconzujian-xinwenbobao',
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
        cname: '新闻公告',
        name: 'news',
        timestamp: this.num,
        isHide: false,
        setUp: {
          tabVal: 0,
        },
        titleLeft: '展示设置',
        titleStyle: '公告风格',
        titleButton: '按钮设置',
        titleContent: '公告内容',
        titleRight: '标题样式',
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
          ],
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
          url: require('@/assets/images/news2.png'),
          type: 'code',
          delType: 0,
          name: '上传图片',
        },
        titleTxtConfig: {
          title: '标题文字',
          value: '商城头条',
          place: '请输入标题文字',
          max: 4,
        },
        rollConfig: {
          title: '滚动方式',
          tabVal: 0,
          tabList: [
            {
              name: '上下滚动',
            },
            {
              name: '左右滚动',
            },
          ],
        },
        buttonConfig: {
          title: '右侧按钮',
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
        textConfig: {
          title: '右侧文字',
          value: '更多',
          place: '请输入右侧文字',
          max: 4,
        },
        linkConfig: {
          title: '链接',
          value: '',
          place: '选择跳转链接',
          max: 100,
          type: 'link',
        },
        listConfig: {
          max: 10,
          type: 1,
          list: [
            {
              chiild: [
                {
                  title: '标题',
                  val: '标题',
                  max: 20,
                  pla: '输入标题',
                },
                {
                  title: '链接',
                  val: '',
                  max: 200,
                  pla: '输入连接',
                },
              ],
              show: true,
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
        titleBgColor: {
          title: '标题背景',
          default: [
            {
              item: '#FCEAE9',
            },
            {
              item: '#FCEAE9',
            },
          ],
          color: [
            {
              item: '#FCEAE9',
            },
            {
              item: '#FCEAE9',
            },
          ],
        },
        titleColor: {
          title: '标题文字',
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
        newsColor: {
          title: '新闻标题',
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
        bntColor: {
          title: '按钮颜色',
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
          val: 0,
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
          val: 0,
          min: 0,
          valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
        },
        // logoConfig: {
        //     header: '图标设置',
        //     title: '最多可添加1张图片，建议宽度130 * 36px',
        //     url: require('@/assets/images/news.png')
        // }
      },
      tabVal: '',
      rollStyle: '',
      txtPosition: '',
      pageData: {},

      list: [],
      slider: 0,
      styleConfig: 0,
      imgUrl: '',
      buttonConfig: 0,
      textConfig: '',
      toneConfig: 0,
      newsColor: '',
      bntColor: '',
      bgColorLeft: '',
      bgColorRight: '',
      bottomBgColor: '',
      topConfig: 0,
      bottomConfig: 0,
      prConfig: 0,
      bgRadius: 0,
      titleConfig: 0,
      titleBgColorLeft: '',
      titleBgColorRight: '',
      titleColor: '',
      titleTxtConfig: '',
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
        this.slider = data.mbConfig.val;
        this.styleConfig = data.styleConfig.tabVal;
        this.imgUrl = data.imgConfig.url;
        this.buttonConfig = data.buttonConfig.tabVal;
        this.textConfig = data.textConfig.value;
        let lists = data.listConfig.list;
        let list = [];
        lists.forEach((item) => {
          if (item.show) {
            list.push(item);
          }
        });
        console.log(data.titleColor,'data.titleColor')
        this.list = list;
        this.toneConfig = data.toneConfig.tabVal;
        this.newsColor = data.newsColor.color[0].item;
        this.bntColor = data.bntColor.color[0].item;
        this.bgColorLeft = data.moduleColor.color[0].item;
        this.bgColorRight = data.moduleColor.color[1].item;
        this.bottomBgColor = data.bottomBgColor.color[0].item;
        this.prConfig = data.prConfig.val;
        this.topConfig = data.topConfig.val;
        this.bottomConfig = data.bottomConfig.val;
        this.titleConfig = data.titleConfig.tabVal;
        this.titleBgColorLeft = data.titleBgColor.color[0].item;
        this.titleBgColorRight = data.titleBgColor.color[1].item;
        this.titleColor = data.titleColor.color[0].item;
        this.titleTxtConfig = data.titleTxtConfig.value;
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
.pageOn {
  border-radius: 6px !important;
}
.news-box {
  .list {
    padding: 0 12px 16px 12px;
    .title {
      color: #999999;
      font-size: 12px;
      padding: 16px 0;

      .top {
        font-size: 16px;
      }

      .pictrue {
        height: 18px;
        img {
          height: 100%;
          width: 100%;
        }
      }
      .iconfont {
        font-size: 12px;
      }
    }
    .text {
      color: #282828;
      font-size: 14px;

      .num {
        font-size: 15px;
        margin-right: 7px;
        color: #999999;
        &.on {
          color: #e93323;
        }
        &.on2 {
          color: #ff7300;
        }
        &.on3 {
          color: #ffc300;
        }
      }

      & ~ .text {
        margin-top: 12px;
      }
    }
  }
  .item {
    display: flex;
    align-items: center;
    height: 44px;
    padding: 0 10px;
    font-size: 13px;
    color: #333;
    position: relative;
    .top {
      max-width: 64px;
      height: 20px;
      font-size: 13px;
      text-align: center;
      line-height: 20px;
      padding: 0 6px;
      border-radius: 4px;
      margin-right: 8px;
    }
    .iconfont {
      color: #999;
      font-size: 14px;
    }
    .img-box {
      height: 24px;
      img {
        height: 100%;
        margin-right: 8px;
      }
    }
    .right-box {
      flex: 1;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
  }
}
</style>
