<template>
  <div v-if="bgColor" :style="{ padding: '0 ' + prConfig + 'px' }">
    <div
      class="coupon"
      :class="bgStyle === 0 ? '' : 'couponOn'"
      :style="{ background: bgColor[0].item, marginTop: mTOP + 'px' }"
      v-if="bgColor.length > 0"
    >
      <div class="item" :style="{ background: themeColor[0].item }">
        <div class="left">
          <div class="num"><span>￥</span>50</div>
          <div class="txt">满100元可用</div>
        </div>
        <div class="right">立<br />即<br />领<br />取</div>
        <div class="roll up-roll" :style="{ background: bgColor[0].item }"></div>
        <div class="roll down-roll" :style="{ background: bgColor[0].item }"></div>
      </div>
      <div class="item gary">
        <div class="left">
          <div class="num"><span>￥</span>50</div>
          <div class="txt">满100元可用</div>
        </div>
        <div class="right">立<br />即<br />领<br />取</div>
        <div class="roll up-roll" :style="{ background: bgColor[0].item }"></div>
        <div class="roll down-roll" :style="{ background: bgColor[0].item }"></div>
      </div>
      <div class="item" :style="{ background: themeColor[0].item }">
        <div class="left">
          <div class="num"><span>￥</span>50</div>
          <div class="txt">满100元可用</div>
        </div>
        <div class="right">立<br />即<br />领<br />取</div>
        <div class="roll up-roll" :style="{ background: bgColor[0].item }"></div>
        <div class="roll down-roll" :style="{ background: bgColor[0].item }"></div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex';
export default {
  name: 'home_coupon',
  cname: '优惠券',
  configName: 'c_home_coupon',
  icon: 'iconyouhuiquan1',
  type: 1, // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'coupon', // 外面匹配名称
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
        name: 'coupon',
        timestamp: this.num,
        bgColor: {
          title: '背景颜色',
          default: [
            {
              item: '#F8F8F8',
            },
          ],
          color: [
            {
              item: '#F8F8F8',
            },
          ],
        },
        bgStyle: {
          title: '背景样式',
          name: 'bgStyle',
          type: 0,
          list: [
            {
              val: '直角',
              icon: 'iconPic_square',
            },
            {
              val: '圆角',
              icon: 'iconPic_fillet',
            },
          ],
        },
        prConfig: {
          title: '背景边距',
          val: 0,
          min: 0,
        },
        themeColor: {
          title: '主题颜色',
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
        // 页面间距
        mbConfig: {
          title: '页面间距',
          val: 0,
          min: 0,
        },
      },
      pageData: {},
      bgColor: [],
      mTOP: 0,
      themeColor: [],
      bgStyle: 0,
      prConfig: 0,
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
        this.mTOP = data.mbConfig.val;
        this.themeColor = data.themeColor.color;
        this.bgStyle = data.bgStyle.type;
        this.prConfig = data.prConfig.val;
      }
      // this.edge = data.lrConfig.val;
      // this.imgStyle = data.imgConfig.type
      // this.imgSrc = data.swiperConfig.list[0].img
    },
  },
};
</script>

<style scoped lang="stylus">
.couponOn{
    border-radius 10px
}
.coupon
    display flex
    align-items center
    padding 15px 0 15px 10px
    background #F8F8F8
    overflow hidden
    .item
        flex-shrink 0
        position relative
        display flex
        width:152px;
        height:76px;
        background:rgba(233,51,35,1);
        color #fff
        border-radius 5px
        margin-right 10px
        &.gary
            background #D8D8D8
        .left
            width 120px
            height 76px
            display flex
            flex-direction column
            align-items center
            justify-content center
            .num
                font-size 24px
                font-weight bold
                span
                    font-size 12px
            .txt
                font-size 12px
        .right
            flex 1
            display flex
            align-items center
            justify-content center
            font-size 12px
            border-left 1px dashed #fff
        .roll
            position absolute
            width 10px
            height 10px
            border-radius 50%
            background #F8F8F8
            &.up-roll
                right: 26px;
                top: -5px;
            &.down-roll
                right: 26px;
                bottom: -5px;
</style>
