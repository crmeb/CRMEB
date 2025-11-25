<template>
  <div class="service-box" :class="positions ? '' : 'on'" :style="{ marginTop: mTop + 'px' }">
    <div class="img-box">
      <img :src="imgUrl" alt="" v-if="imgUrl" />
      <div class="empty-box on" v-else>
        <img src="../../assets/images/shan.png" />
      </div>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex';

export default {
  name: 'home_service',
  cname: '悬浮按钮',
  configName: 'c_home_service',
  icon: '#iconzujian-xuanfuanniu',
  type: 2, // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'customerService', // 外面匹配名称
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
  data() {
    return {
      defaultConfig: {
        cname: '悬浮按钮',
        name: 'customerService',
        timestamp: this.num,
        isHide: false,
        setUp: {
          tabVal: 0,
        },
        titleLeft: '按钮设置',
        titleRight: '位置设置',
        buttonConfig: {
          title: '按钮跳转',
          tabVal: 0,
          tabList: [
            {
              name: '页面链接',
            },
            {
              name: '客服入口',
            },
          ],
        },
        locationConfig: {
          title: '展示位置',
          tabVal: 1,
          tabList: [
            {
              name: '左',
            },
            {
              name: '右',
            },
          ],
        },
        logoConfig: {
          title: '建议：展示上传100*100px；',
          url: '',
          link: '',
        },
        // 页面间距
        topConfig: {
          title: '上下偏移',
          val: 0,
          min: 0,
        },
      },
      imgUrl: '',
      pageData: {},
      mTop: 0,
      positions: 1, //展示位置
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
      if (data.topConfig) {
        this.mTop = data.topConfig.val;
        this.imgUrl = data.logoConfig.url;
        this.positions = data.locationConfig.tabVal;
      }
    },
  },
};
</script>

<style scoped lang="scss">
.service-box {
  width: 100%;
  display: flex;
  justify-content: flex-end;
  padding-right: 10px;
  &.on {
    justify-content: flex-start;
    padding-left: 10px;
  }
  .img-box {
    width: 43px;
    height: 43px;
    img {
      width: 100%;
      height: 100%;
      border-radius: 50%;
    }
    .empty-box {
      border-radius: 50%;
      background: #f3f9ff;
      img {
        width: 26px;
        height: 20px;
      }
      .iconfont-diy {
        font-size: 20px;
      }
    }
  }
}
</style>
