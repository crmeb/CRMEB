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
    <div
      class="pictrue acea-row row-center-wrapper"
      :class="scaleConfig == 1 ? 'on' : scaleConfig == 2 ? 'on2' : ''"
      :style="'background-image:url(' + (imgUrl ? imgUrl : imgBgUrl) + ');border-radius:' + bgRadius"
    >
      <div class="image acea-row row-center-wrapper">
        <img src="../../assets/images/ic_right2.png" />
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, mapMutations } from 'vuex';
export default {
  name: 'home_video',
  cname: '视频',
  configName: 'c_video',
  icon: '#iconzujian-shipin',
  type: 0, // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'videos', // 外面匹配名称
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
        cname: '视频',
        name: 'videos',
        timestamp: this.num,
        isHide: false,
        setUp: {
          tabVal: 0,
        },
        titleLeft: '内容设置',
        titleRight: '通用样式',
        imgConfig: {
          url: '',
          type: 'code',
          delType: 1,
          name: '视频封面',
        },
        videoConfig: {
          url: '',
          type: 'code',
          video: 1,
          delType: 0,
          name: '上传视频',
        },
        scaleConfig: {
          title: '视频比例',
          tabVal: 0,
          tabList: [
            {
              name: '16:9',
            },
            {
              name: '4:3',
            },
            {
              name: '1:1',
            },
          ],
        },
        bottomBgColor: {
          title: '底部背景',
          name: 'bgColor',
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
      },
      imgBgUrl: require('@/assets/images/videoBg.png'),
      bottomBgColor: '',
      confObj: {},
      pageData: {},
      topConfig: '',
      bottomConfig: '',
      prConfig: 0,
      bgRadius: 0,
      imgUrl: '',
      scaleConfig: 0,
      mTop: 0,
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
        this.imgUrl = data.imgConfig.url;
        this.scaleConfig = data.scaleConfig.tabVal;
        this.bottomBgColor = data.bottomBgColor.color[0].item;
        this.topConfig = data.topConfig.val;
        this.bottomConfig = data.bottomConfig.val;
        this.prConfig = data.prConfig.val;
        this.mTop = data.mbConfig.val;
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
.pictrue {
  width: 100%;
  height: 213px;
  background-repeat: no-repeat;
  background-size: cover;
  background-position: 50%;
  &.on {
    height: 284px;
  }
  &.on2 {
    height: 379px;
  }
  .image {
    width: 44px;
    height: 44px;
    background: rgba(0, 0, 0, 0.2);
    border-radius: 50%;
    img {
      width: 24px;
      height: 24px;
      display: block;
    }
  }
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
