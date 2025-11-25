<template>
  <div
    :style="{
      background: bottomBgColor,
      paddingTop: topConfig + 'px',
      paddingBottom: bottomConfig + 'px',
      paddingLeft: prConfig + 'px',
      paddingRight: prConfig + 'px',
    }"
  >
    <div
      class="search-box"
      :style="{
        background: `linear-gradient(90deg,${bgColorLeft} 0%,${bgColorRight} 100%)`,
        borderRadius: bgRadius,
      }"
    >
      <div class="search acea-row row-middle" :style="[txtPosition]">
        <img :src="logoUrl" alt="" v-if="logoUrl && styleConfig == 0 && styleTypeConfig == 1" />
        <div
          class="title"
          :style="[txtStyle]"
          v-if="titleConfig && (styleConfig == 1 || (styleConfig == 0 && styleTypeConfig == 0))"
        >
          {{ titleConfig }}
        </div>
        <div v-if="styleConfig === 0" class="box" :style="[searchStyle]">
          <span
            class="iconfont iconsousuo1"
            :style="{
              color: tipColor,
            }"
          ></span>
          <span
            class="hotWords"
            :style="{
              color: hotWordsColor,
            }"
            v-if="hotWords"
            >{{ hotWords }}</span
          >
          <span
            v-else
            :style="{
              color: tipColor,
            }"
            >{{ tipConfig }}</span
          >
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex';
// import theme from "@/mixins/theme";
export default {
  name: 'search_box',
  cname: '搜索框',
  icon: '#iconzujian-sousuokuang',
  configName: 'c_search_box',
  type: 0, // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'headerSerch', // 外面匹配名称
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
    txtStyle() {
      let num = 0;
      if (this.styleConfig == 0 && this.styleTypeConfig != 1) {
        num = 15;
      }
      return {
        color: `${this.txtColor}`,
        fontStyle: `${this.txtStyleConfig != 'bold' ? this.txtStyleConfig : ''}`,
        fontWeight: `${this.txtStyleConfig == 'bold' ? this.txtStyleConfig : ''}`,
        fontSize: `${this.txtSize}px`,
        marginRight: `${num}px`,
      };
    },
    txtPosition() {
      return {
        justifyContent:
          this.styleConfig != 0 && this.txtFixConfig === 1
            ? 'center'
            : this.styleConfig != 0 && this.txtFixConfig === 2
            ? 'flex-end'
            : 'flex-start',
      };
    },
    searchStyle() {
      return {
        textAlign: this.txtFixConfig == 0 ? 'left' : this.txtFixConfig == 2 ? 'right' : 'center',
        background: this.searchBoxColor,
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
        cname: '搜索框',
        name: 'headerSerch',
        timestamp: this.num,
        isHide: false,
        setUp: {
          tabVal: 0,
        },
        titleLeft: '展示设置',
        titleSearch: '搜索内容',
        titleHotWords: '搜索热词',
        titleRight: '搜索框',
        titleCurrency: '通用样式',
        titleTxt: '文字设置',
        styleConfig: {
          title: '选择风格',
          tabVal: 0,
          tabList: [
            {
              name: '搜索',
            },
            {
              name: '标题',
            },
          ],
        },
        styleTypeConfig: {
          title: '样式类型',
          tabVal: 1,
          tabList: [
            {
              name: '标题',
            },
            {
              name: 'logo',
            },
          ],
        },
        logoConfig: {
          info: '建议：144px * 44px',
          url: '',
          type: 'code',
          delType: 1,
          name: 'logo图',
        },
        titleConfig: {
          title: '标题',
          value: '标题',
          place: '请输入标题',
          max: 6,
        },
        linkConfig: {
          title: '链接',
          value: '',
          place: '请选择链接',
          max: 100,
          type: 'link',
        },
        tipConfig: {
          title: '提示文字',
          value: '搜索商品',
          place: '填写内容',
          max: 20,
        },
        hotWords: {
          list: [
            {
              val: '',
            },
          ],
        },
        numConfig: {
          placeholder: '设置搜索热词显示时间',
          title: '显示时间',
          val: 3,
          type: 'words',
        },
        txtFixConfig: {
          title: '文字位置',
          tabVal: 0,
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
        txtStyleConfig: {
          title: '文字样式',
          tabVal: 0,
          tabList: [
            {
              name: '正常',
              style: 'normal',
            },
            {
              name: '倾斜',
              style: 'italic',
            },
            {
              name: '加粗',
              style: 'bold',
            },
          ],
        },
        txtColor: {
          title: '文字颜色',
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
        txtSize: {
          title: '文字大小',
          val: 15,
          min: 0,
        },
        searchBoxColor: {
          title: '搜索框',
          default: [
            {
              item: '#F5F5F5',
            },
          ],
          color: [
            {
              item: '#F5F5F5',
            },
          ],
        },
        tipColor: {
          title: '提示文字',
          default: [
            {
              item: '#CCCCCC',
            },
          ],
          color: [
            {
              item: '#CCCCCC',
            },
          ],
        },
        hotWordsColor: {
          title: '热词文字',
          default: [
            {
              item: '#888',
            },
          ],
          color: [
            {
              item: '#888',
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
      logoUrl: '',
      styleConfig: 0,
      bottomBgColor: '',
      bgColorLeft: '',
      bgColorRight: '',
      topConfig: 0,
      bottomConfig: 0,
      prConfig: 0,
      bgRadius: 0,
      titleConfig: '',
      searchBoxColor: '',
      tipConfig: '',
      hotWords: '',
      tipColor: '',
      hotWordsColor: '',
      styleTypeConfig: 0,
      fixConfig: 0,
      txtFixConfig: 0,
      txtColor: '',
      txtStyleConfig: '',
      txtSize: 0,
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
      if (data.prConfig) {
        this.logoUrl = data.logoConfig.url;
        this.styleConfig = data.styleConfig.tabVal;
        this.styleTypeConfig = data.styleTypeConfig.tabVal;
        // this.fixConfig = data.fixConfig.tabVal || 0;
        this.txtFixConfig = data.txtFixConfig.tabVal;
        this.txtStyleConfig = data.txtStyleConfig.tabList[data.txtStyleConfig.tabVal].style;
        this.txtSize = data.txtSize.val;
        this.txtColor = data.txtColor.color[0].item;
        this.bottomBgColor = data.bottomBgColor.color[0].item;
        this.bgColorLeft = data.moduleColor.color[0].item;
        this.bgColorRight = data.moduleColor.color[1].item;
        this.topConfig = data.topConfig.val;
        this.bottomConfig = data.bottomConfig.val;
        this.prConfig = data.prConfig.val;
        this.titleConfig = data.titleConfig.value;
        this.searchBoxColor = data.searchBoxColor.color[0].item;
        this.tipConfig = data.tipConfig.value;
        this.hotWords = data.hotWords.list.length ? data.hotWords.list[0].val : '';
        this.tipColor = data.tipColor.color[0].item;
        this.hotWordsColor = data.hotWordsColor.color[0].item;
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
.search-box {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 48px;
  padding: 9px 15px;
  cursor: pointer;
  .search {
    width: 100%;
    &.center {
      justify-content: center;
    }
    &.right {
      justify-content: right;
    }
    .hotWords {
      color: rgba(255, 255, 255, 0.8);
    }
  }
  .title {
    font-size: 15px;
    color: #333;
  }
  .map {
    color: #333;
    font-size: 14px;
    .iconfont {
      font-size: 16px;
    }
    .iconyou {
      font-size: 12px;
      opacity: 0.8;
    }
    .icondingwei {
      margin-right: 3px;
    }
  }
  img {
    width: 76px;
    height: 30px;
    margin-right: 11px;
  }
  .box {
    flex: 1;
    height: 30px;
    line-height: 30px;
    color: #ccc;
    font-size: 14px;
    background: #fff;
    border-radius: 15px;
    padding: 0 16px;

    .iconfont {
      margin-right: 5px;
      margin-top: -3px;
      display: inline-block;
      vertical-align: middle;
    }
  }
}
</style>
