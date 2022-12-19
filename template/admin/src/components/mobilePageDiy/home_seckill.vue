<template>
  <div :style="{ padding: '0 ' + prConfig + 'px' }">
    <div
      class="seckill-box"
      :class="conStyle ? '' : 'seckillOn'"
      :style="{ background: bgColor, marginTop: mTOP + 'px' }"
    >
      <div class="hd">
        <div class="left">
          <img :src="imgUrl" alt="" />
          <p>限时秒杀</p>
          <div class="time">
            <span :style="{ background: countDownColor, color: themeColor }">00</span>
            <em>:</em>
            <span :style="{ background: countDownColor, color: themeColor }">00</span>
            <em>:</em>
            <span :style="{ background: countDownColor, color: themeColor }">00</span>
          </div>
        </div>
        <div class="right">更多</div>
      </div>
      <div class="list-wrapper">
        <div class="list-item" v-for="(item, index) in list" :index="index" :style="{ marginRight: listRight + 'px' }">
          <div class="img-box">
            <img :src="item.img" alt="" v-if="item.img" />
            <div class="empty-box"><span class="iconfont-diy icontupian"></span></div>
            <div v-if="discountShow" class="discount" :style="{ borderColor: themeColor, color: themeColor }">
              {{ item.discount }}折起
            </div>
          </div>
          <div class="title line1" v-if="titleShow">{{ item.name }}</div>
          <div class="price">
            <span class="label" :style="{ background: themeColor }" v-if="seckillShow">抢</span>
            <span class="num-label" :style="{ color: themeColor }" v-if="priceShow">￥</span>
            <span class="num" :style="{ color: themeColor }" v-if="priceShow">{{ item.price }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, mapMutations } from 'vuex';
export default {
  name: 'home_seckill',
  cname: '秒杀',
  configName: 'c_home_seckill',
  icon: 'iconmiaosha1',
  type: 1, // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'seckill', // 外面匹配名称
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
        name: 'seckill',
        timestamp: this.num,
        setUp: {
          tabVal: 0,
        },
        countDownColor: {
          title: '倒计时背景色',
          name: 'countDownColor',
          default: [
            {
              item: 'rgba(252,60,62,0.09)',
            },
          ],
          color: [
            {
              item: 'rgba(252,60,62,0.09)',
            },
          ],
        },
        themeColor: {
          title: '主题风格',
          name: 'themeColor',
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
        conStyle: {
          title: '背景样式',
          name: 'conStyle',
          type: 1,
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
        bgColor: {
          title: '背景颜色',
          name: 'themeColor',
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
        prConfig: {
          title: '背景边距',
          val: 10,
          min: 0,
        },
        priceShow: {
          title: '是否显示价格',
          val: true,
        },
        discountShow: {
          title: '是否显示折扣标签',
          val: true,
        },
        titleShow: {
          title: '是否显示名称',
          val: true,
        },
        seckillShow: {
          title: '抢购标签',
          val: true,
        },
        numberConfig: {
          val: 3,
        },
        lrConfig: {
          title: '左右边距',
          val: 10,
          min: 0,
        },
        // 页面间距
        mbConfig: {
          title: '页面间距',
          val: 0,
          min: 0,
        },
        imgConfig: {
          title: '最多可添加1张图片，建议宽度18 * 18px',
          url: 'http://pro.crmeb.net/static/images/spike-icon-002.gif',
        },
      },
      list: [
        {
          img: '',
          name: '小米家用电饭煲小米家用电饭煲',
          price: '234',
          discount: '1.2',
        },
        {
          img: '',
          name: '小米家用电饭煲小米家用电饭煲',
          price: '234',
          discount: '1.2',
        },
        {
          img: '',
          name: '小米家用电饭煲小米家用电饭煲',
          price: '234',
          discount: '1.2',
        },
      ],
      mTOP: 0,
      listRight: 0,
      countDownColor: '',
      themeColor: '',
      pageData: {},
      imgUrl: '',
      priceShow: true,
      discountShow: true,
      titleShow: true,
      seckillShow: true,
      prConfig: 0,
      bgColor: '',
      conStyle: 1,
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
        this.mTOP = data.mbConfig.val;
        this.listRight = data.lrConfig.val;
        this.countDownColor = data.countDownColor.color[0].item;
        this.themeColor = data.themeColor.color[0].item;
        this.imgUrl = data.imgConfig.url;
        this.priceShow = data.priceShow.val;
        this.discountShow = data.discountShow.val;
        this.titleShow = data.titleShow.val;
        this.seckillShow = data.seckillShow.val;
        this.prConfig = data.prConfig.val;
        this.bgColor = data.bgColor.color[0].item;
        this.conStyle = data.conStyle.type;
      }
    },
  },
};
</script>

<style scoped lang="stylus">
.seckillOn{
  border-radius 0!important
}
.pageOn{
   border-radius 10px!important
}
.seckill-box
    padding 15px 10px
    background #fff
    border-radius 10px
    .hd
        display flex
        justify-content space-between
        align-items center
        .left
            display flex
            align-items center
            img
                width 18px
                height 18px
                margin-right 5px
                border-radius 50%
            p
                font-size 16px
                color #282828
                font-weight:600;
            .time
                display flex
                align-items center
                margin-left 5px
                color #FF4444
                span
                    width 20px
                    height 16px
                    font-size 12px
                    text-align center
                    line-height 16px
                em
                    font-size 12px
                    margin 0 3px
                    font-style: initial;
                    font-weight bold
    .list-wrapper
        display flex
        margin-top 8px
        overflow hidden
        .list-item
            flex-shrink 0
            width 110px
            background-color: #fff;
            .img-box
                position: relative;
                width 100%
                height 110px
                img,.box
                    width 100%
                    height 100%
                    border-radius:8px;
                .box
                    background #D8D8D8
                .discount
                    position absolute
                    left 8px
                    bottom 8px
                    height:18px;
                    padding 0 3px
                    line-height 18px
                    background:rgba(255,255,255,1);
                    border-radius:2px;
                    border:1px solid transparent;
                    font-size 12px
            .title
                margin-top 5px
                font-size 13px
                color #282828
                padding: 0 3px;
            .price
                display flex
                align-items center
                padding: 0 3px;
                .label
                    font-size 9px
                    width 16px
                    height 16px
                    color #fff
                    text-align center
                    line-height 16px
                .num-label
                    color #FF4444
                    font-size 12px
                    font-weight:600;
                    margin 1px 2px 0
                .num
                    color #FF4444
                    font-size 16px
                    font-weight:600;
</style>
