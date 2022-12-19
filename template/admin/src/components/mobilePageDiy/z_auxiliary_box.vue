<template>
  <div class="mobile-page">
    <div class="box" :style="{ height: cSlider + 'px', background: bgColor }"></div>
  </div>
</template>

<script>
import { mapState, mapMutations } from 'vuex';
export default {
  name: 'z_auxiliary_box',
  cname: '辅助空白',
  configName: 'c_auxiliary_box',
  icon: 'iconfuzhukongbai1',
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
        name: 'blankPage',
        timestamp: this.num,
        bgColor: {
          title: '背景颜色',
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
        heightConfig: {
          title: '组件高度',
          val: 10,
          min: 1,
        },
      },
      cSlider: '',
      bgColor: '',
      confObj: {},
      pageData: {},
      edge: '',
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
      }
    },
  },
};
</script>

<style scoped lang="stylus">
.box
    height 20px
    background #F5F5F5
</style>
