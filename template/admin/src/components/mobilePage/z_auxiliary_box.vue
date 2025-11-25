<template>
  <div
    class="mobile-page"
    :style="{
      background: bottomBgColor,
      paddingTop: topConfig + 'px',
      paddingBottom: bottomConfig + 'px',
      paddingLeft: lrEdge + 'px',
      paddingRight: lrEdge + 'px',
    }"
  >
    <div
      class="box"
      :style="{
        height: cSlider + 'px',
        background: bgColor,
        borderRadius: fillet
          ? valList[0].val + 'px ' + valList[1].val + 'px ' + valList[3].val + 'px ' + valList[2].val + 'px'
          : filletVal + 'px',
      }"
    ></div>
  </div>
</template>

<script>
import { mapState, mapMutations } from 'vuex';
export default {
  name: 'z_auxiliary_box',
  cname: '辅助空白',
  configName: 'c_auxiliary_box',
  icon: '#iconzujian-fuzhukongbai',
  type: 2, // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'blankPage', // 外面匹配名称
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
        cname: '辅助空白',
        name: 'blankPage',
        timestamp: this.num,
        isHide: false,
        setUp: {
          tabVal: 0,
        },
        titleLeft: '高度设置',
        titleRight: '通用样式',
        bgColor: {
          title: '组件背景',
          name: 'bgColor',
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
        bottomBgColor: {
          title: '底部背景',
          name: 'bgColor',
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
        heightConfig: {
          title: '组件高度',
          val: 10,
          min: 1,
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
        lrEdge: {
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
      cSlider: '',
      bgColor: '',
      bottomBgColor: '',
      confObj: {},
      pageData: {},
      topConfig: '',
      bottomConfig: '',
      lrEdge: '',
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
      if (data.heightConfig) {
        this.cSlider = data.heightConfig.val;
        this.bgColor = data.bgColor.color[0].item;
        this.bottomBgColor = data.bottomBgColor.color[0].item;
        this.topConfig = data.topConfig.val;
        this.bottomConfig = data.bottomConfig.val;
        this.lrEdge = data.lrEdge.val;
        this.fillet = data.fillet.type;
        this.filletVal = data.fillet.val;
        this.valList = data.fillet.valList;
      }
    },
  },
};
</script>

<style scoped lang="scss">
.box {
  height: 20px;
  background: #f5f5f5;
}
</style>
