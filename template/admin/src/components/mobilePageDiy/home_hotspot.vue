<template>
  <div
    class="mobile-page"
    :style="{
      background: bottomBgColor,
      marginTop: mTop + 'px',
      paddingTop: topConfig + 'px',
      paddingBottom: bottomConfig + 'px',
      paddingLeft: prConfig + 'px',
      paddingRight: prConfig + 'px',
    }"
  >
    <div class="pictrue">
      <img
        :src="imgUrl"
        v-if="imgUrl"
        :style="{
          borderRadius: bgRadius,
        }"
      />
      <div
        class="empty-box"
        v-else
        :style="{
          borderRadius: bgRadius,
        }"
      >
        <img src="@/assets/images/image_default.png" />
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, mapMutations } from 'vuex';
export default {
  name: 'home_hotspot',
  cname: '热区',
  configName: 'c_hotspot',
  icon: 'iconrequ1',
  type: 0, // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'hotspot', // 外面匹配名称
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
        cname: '热区',
        name: 'hotspot',
        timestamp: this.num,
        isHide: false,
        setUp: {
          tabVal: 0,
        },
        titleLeft: '内容设置',
        titleRight: '通用样式',
        picStyle: {
          url: '',
          list: [],
        },
        mbConfig: {
          title: '页面间距',
          val: 0,
          min: 0,
        },
        bgColor: {
          title: '背景颜色',
          name: 'bgColor',
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
        // bottomBgColor: {
        //   title: '底部背景',
        //   name: 'bottomBgColor',
        //   default: [
        //     {
        //       item: '#F5F5F5',
        //     },
        //   ],
        //   color: [
        //     {
        //       item: '#F5F5F5',
        //     },
        //   ],
        // },
        // topConfig: {
        //   title: '上边距',
        //   val: 0,
        //   min: 0,
        // },
        // bottomConfig: {
        //   title: '下边距',
        //   val: 0,
        //   min: 0,
        // },
        // prConfig: {
        //   title: '左右边距',
        //   val: 0,
        //   min: 0,
        // },
        // mbConfig: {
        //   title: '页面上间距',
        //   val: 0,
        //   min: 0,
        // },
        // fillet: {
        //   title: '背景圆角',
        //   type: 0,
        //   list: [
        //     {
        //       val: '全部',
        //       icon: 'iconcaozuo-zhengti',
        //     },
        //     {
        //       val: '单个',
        //       icon: 'iconcaozuo-bianjiao',
        //     },
        //   ],
        //   valName: '圆角值',
        //   val: 0,
        //   min: 0,
        //   valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
        // },
      },
      bottomBgColor: '',
      confObj: {},
      pageData: {},
      topConfig: '',
      bottomConfig: '',
      prConfig: 0,
      bgRadius: 0,
      imgUrl: '',
      mTop: 0,
      list: [],
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
      this.imgUrl = data.picStyle.url;
      this.list = data.picStyle?.list;
      // this.bottomBgColor = data.bottomBgColor.color[0].item;
      // this.topConfig = data.topConfig.val;
      // this.bottomConfig = data.bottomConfig.val;
      // this.prConfig = data.prConfig.val;
      this.mTop = data.mbConfig.val;
      // let fillet = data.fillet.type;
      // let filletVal = data.fillet.val;
      // let valList = data.fillet.valList;
      // this.bgRadius = fillet
      //   ? valList[0].val + 'px ' + valList[1].val + 'px ' + valList[3].val + 'px ' + valList[2].val + 'px'
      //   : filletVal + 'px';
    },
  },
};
</script>

<style lang="scss" scoped>
.pictrue {
  width: 100%;
  height: 100%;
  .empty-box {
    width: 100%;
    height: 379px;
    border-radius: 0;
    background: #f3f9ff;

    img {
      width: 65px;
      height: 50px;
    }
  }
  img {
    width: 100%;
    height: 100%;
  }
}
</style>
