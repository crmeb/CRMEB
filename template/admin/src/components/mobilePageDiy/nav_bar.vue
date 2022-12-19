<template>
  <div class="mobile-page">
    <div
      class="menus"
      :style="{
        background: `linear-gradient(90deg,${bgColor[0].item} 0%,${bgColor[1].item} 100%)`,
        marginTop: `${cSlider}px`,
      }"
      v-if="bgColor.length > 0"
    >
      <div class="item" v-for="(item, index) in list" :class="{ on: curIndex == index }" :style="{ color: txtColor }">
        {{ item.name }} <span :style="{ background: txtColor }"></span>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, mapMutations } from 'vuex';
export default {
  name: 'nav_bar',
  configName: 'c_nav_bar',
  cname: '商品分类',
  icon: 'iconfenleidaohang1',
  type: 0, // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'tabNav', // 外面匹配名称
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
      // 默认初始化数据禁止修改
      defaultConfig: {
        name: 'tabNav',
        timestamp: this.num,
        status: {
          title: '开关',
          default: {
            status: false,
          },
        },
        txtColor: {
          title: '文字颜色',
          name: 'txtColor',
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
        bgColor: {
          title: '背景颜色',
          name: 'bgColor',
          default: [
            {
              item: '#F62C2C',
            },
            {
              item: '#F96E29',
            },
          ],
          color: [
            {
              item: '#F62C2C',
            },
            {
              item: '#F96E29',
            },
          ],
        },
        // 页面间距
        mbConfig: {
          title: '页面间距',
          val: 0,
          min: 0,
        },
      },
      list: [
        {
          name: '精选',
        },
        {
          name: '靓丽美妆',
        },
        {
          name: '母婴',
        },
        {
          name: '珠宝饰品',
        },
        {
          name: '男装',
        },
      ],
      curIndex: 0,
      bgColor: [],
      cSlider: 0,
      txtColor: '',
      status: false,
      pageData: {},
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
        this.bgColor = data.bgColor.color;
        this.cSlider = data.mbConfig.val;
        this.txtColor = data.txtColor.color[0].item;
      }
    },
  },
};
</script>

<style scoped lang="stylus">
.menus {
  display: flex;
  align-items: center;
  width: 100%;
  height: 46px;
  cursor pointer;
  background: linear-gradient(90deg, #F62C2C 0%, #F96E29 100%);

  .item {
    position: relative;
    flex: 1;
    text-align: center;
    color: #fff;

    &.on span {
      display: block;
      position: absolute;
      left: 50%;
      bottom: -5px;
      width: 16px;
      height: 2px;
      transform: translateX(-50%);
      background: #fff;
    }
  }
}
</style>
