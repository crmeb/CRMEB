<template>
  <div
    :style="{
      background: bottomBgColor,
      marginTop: mbConfig + 'px',
      paddingTop: topConfig + 'px',
      paddingBottom: bottomConfig + 'px',
      paddingLeft: prConfig + 'px',
      paddingRight: prConfig + 'px',
    }"
  >
    <div
      class="title"
      :style="{
        background: `linear-gradient(90deg,${titleColorLeft} 0%,${titleColorRight} 100%)`,
        borderRadius: fillet
          ? valList[0].val + 'px ' + valList[1].val + 'px ' + valList[3].val + 'px ' + valList[2].val + 'px'
          : filletVal + 'px',
      }"
    >
      <div
        class="title-box"
        :class="buttonConfig ? 'on' : ''"
        :style="{
          color: themeColor,
          fontSize: fontSize + 'px',
          fontStyle: txtStyle != 'bold' ? txtStyle : '',
          fontWeight: txtStyle == 'bold' ? txtStyle : '',
          textAlign: txtPosition,
        }"
      >
        {{ titleTxt }}
      </div>
      <div
        v-if="!buttonConfig"
        :style="{
          color: buttonColor,
          fontSize: buttonSize + 'px',
        }"
      >
        {{ buttonTitle }}<span class="iconfont iconjinru"></span>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex';
export default {
  name: 'home_title',
  cname: '文本标题',
  icon: '#iconzujian-biaoti',
  configName: 'c_home_title',
  type: 2, // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'titles', // 外面匹配名称
  props: {
    index: {
      type: null,
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
      defaultConfig: {
        cname: '文本标题',
        name: 'titles',
        timestamp: this.num,
        isHide: false,
        setUp: {
          tabVal: 0,
        },
        titleLeft: '标题设置',
        titleRight: '文字设置',
        titleCurrency: '通用样式',
        titleConfig: {
          title: '标题名称',
          value: '标题',
          place: '请输入标题',
          max: 10,
        },
        titleConfigRight: {
          title: '右侧文字',
          value: '更多',
          place: '请输入右侧文字',
          max: 5,
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
        linkConfig: {
          title: '链接',
          value: '',
          place: '请输入链接地址',
          max: 100,
          type: 'link',
        },
        themeColor: {
          title: '标题颜色',
          name: 'themeColor',
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
        buttonColor: {
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
        titleColor: {
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
        buttonText: {
          title: '按钮文字',
          val: 12,
          min: 6,
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
        textPosition: {
          title: '标题位置',
          tabVal: 0,
          tabList: [
            {
              name: '左对齐',
              style: 'left',
              icon: 'icondoc_left',
            },
            {
              name: '居中对齐',
              style: 'center',
              icon: 'icondoc_center',
            },
            {
              name: '右对齐',
              style: 'right',
              icon: 'icondoc_right',
            },
          ],
        },
        textStyle: {
          title: '标题样式',
          tabVal: 0,
          tabList: [
            {
              name: '正常',
              style: 'normal',
              icon: 'icondoc_general',
            },
            {
              name: '倾斜',
              style: 'italic',
              icon: 'icondoc_skew',
            },
            {
              name: '加粗',
              style: 'bold',
              icon: 'icondoc_bold',
            },
          ],
        },
        fontSize: {
          title: '标题文字',
          val: 16,
          min: 8,
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
      },
      titleTxt: '',
      link: '',
      txtPosition: '',
      txtStyle: '',
      fontSize: 0,
      titleColorLeft: '',
      titleColorRight: '',
      themeColor: '',
      prConfig: 0,
      pageData: {},
      bottomBgColor: '',
      mbConfig: 0,
      buttonConfig: 0,
      buttonTitle: '',
      buttonColor: '',
      buttonSize: 0,
      topConfig: 0,
      bottomConfig: 0,
      fillet: 0,
      filletVal: 0,
      valList: [],
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
        this.titleTxt = data.titleConfig.value;
        this.link = data.linkConfig.value;
        this.txtPosition = data.textPosition.tabList[data.textPosition.tabVal].style;
        this.txtStyle = data.textStyle.tabList[data.textStyle.tabVal].style;
        this.themeColor = data.themeColor.color[0].item;
        this.fontSize = data.fontSize.val;
        this.mbConfig = data.mbConfig.val;
        this.prConfig = data.prConfig.val;
        this.titleColorLeft = data.titleColor.color[0].item;
        this.titleColorRight = data.titleColor.color[1].item;
        this.bottomBgColor = data.bottomBgColor.color[0].item;
        this.buttonConfig = data.buttonConfig.tabVal;
        this.buttonTitle = data.titleConfigRight.value;
        this.buttonColor = data.buttonColor.color[0].item;
        this.buttonSize = data.buttonText.val;
        this.topConfig = data.topConfig.val;
        this.bottomConfig = data.bottomConfig.val;
        this.fillet = data.fillet.type;
        this.filletVal = data.fillet.val;
        this.valList = data.fillet.valList;
      }
    },
  },
};
</script>

<style scoped lang="scss">
.title {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.titleOn {
  border-radius: 10px !important;
}
.title {
  padding: 13px 12px;
  .title-box {
    &.on {
      width: 100%;
    }
  }
}
.iconfont {
  font-size: 14px;
}
</style>
