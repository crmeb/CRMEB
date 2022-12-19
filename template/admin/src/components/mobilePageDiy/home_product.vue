<template>
  <div class="mobile-page paddingBox" :style="{ marginTop: slider + 'px' }">
    <div class="home_product">
      <div
        class="hd_nav"
        :style="{ justifyContent: titleConfig === 0 ? 'flex-start' : titleConfig === 1 ? 'space-around' : 'flex-end' }"
        v-if="navlist.length"
      >
        <div class="item" v-for="(item, index) in navlist" :index="index">
          <p class="title" :style="{ color: index == tabCur ? activeColor : '' }">{{ item.chiild[0].val }}</p>
          <span
            class="label"
            :style="{ background: index == tabCur ? activeColor : '', color: index == tabCur ? '#fff' : '' }"
            >{{ item.chiild[1].val }}</span
          >
        </div>
      </div>
      <div
        class="hd_nav"
        :style="{ justifyContent: titleConfig === 0 ? 'flex-start' : titleConfig === 1 ? 'space-around' : 'flex-end' }"
        v-else
      >
        <div class="item">
          <p class="title" :style="{ color: index == tabCur ? activeColor : '' }">标题</p>
          <span
            class="label"
            :style="{ background: index == tabCur ? activeColor : '', color: index == tabCur ? '#fff' : '' }"
            >标题简介</span
          >
        </div>
      </div>
      <div class="list-wrapper">
        <div class="item" v-for="(item, index) in list" :index="index">
          <div class="img-box">
            <img v-if="item.image" :src="item.image" alt="" />
            <div v-else class="empty-box"><span class="iconfont-diy icontupian"></span></div>
            <div class="label" :style="{ background: labelColor }" v-if="item.activity && item.activity.type === '1'">
              秒杀
            </div>
            <div class="label" :style="{ background: labelColor }" v-if="item.activity && item.activity.type === '2'">
              砍价
            </div>
            <div class="label" :style="{ background: labelColor }" v-if="item.activity && item.activity.type === '3'">
              拼团
            </div>
          </div>
          <div class="info">
            <div class="title line1" v-if="titleShow">{{ item.store_name }}</div>
            <div class="old-price" v-if="opriceShow">¥{{ item.ot_price }}</div>
            <div class="price">
              <div class="num" :style="{ color: fontColor }" v-if="priceShow"><span>￥</span>{{ item.price }}</div>
              <div
                class="label"
                :style="'border:1px solid ' + labelColor + ';color:' + labelColor"
                :class="priceShow ? '' : 'on'"
                v-if="couponShow && item.checkCoupon"
              >
                券
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex';
export default {
  name: 'home_product',
  cname: '促销列表',
  configName: 'c_home_product',
  icon: 'iconcuxiaoliebiao1',
  type: 0, // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'promotionList', // 外面匹配名称
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
        name: 'promotionList',
        timestamp: this.num,
        setUp: {
          tabVal: 0,
        },
        productList: {
          title: '促销列表',
          list: [],
        },
        titleConfig: {
          title: '标题位置',
          type: 0,
          list: [
            {
              val: '居左',
              icon: 'icondoc_left',
            },
            {
              val: '居中',
              icon: 'icondoc_center',
            },
            {
              val: '居右',
              icon: 'icondoc_right',
            },
          ],
        },
        titleShow: {
          title: '是否显示商品名称',
          val: true,
        },
        opriceShow: {
          title: '是否显示商品原价',
          val: true,
        },
        priceShow: {
          title: '是否显示商品价格',
          val: true,
        },
        couponShow: {
          title: '是否显示优惠券',
          val: true,
        },
        tabConfig: {
          title: '最多可添加4个版块；鼠标拖拽左侧圆点可调整版块顺序',
          max: 4,
          tabCur: 0,
          list: [
            {
              chiild: [
                {
                  title: '标题',
                  val: '首发新品',
                  max: 4,
                  pla: '选填，不超过四个字',
                },
                {
                  title: '简介',
                  val: '最新出炉',
                  max: 4,
                  pla: '选填，不超过四个字',
                },
              ],
              link: {
                title: '链接',
                activeVal: 0,
                optiops: [
                  {
                    type: 0,
                    value: 1,
                    label: '精品推荐',
                  },
                  {
                    type: 1,
                    value: 2,
                    label: '热门榜单',
                  },
                  {
                    type: 2,
                    value: 3,
                    label: '首发新品',
                  },
                  {
                    type: 3,
                    value: 4,
                    label: '促销单品',
                  },
                ],
              },
            },
          ],
        },
        themeColor: {
          title: '主题风格',
          name: 'themeColor',
          default: [
            {
              item: '#F95429',
            },
          ],
          color: [
            {
              item: '#F95429',
            },
          ],
        },
        fontColor: {
          title: '价格颜色',
          name: 'fontColor',
          default: [
            {
              item: '#e93323',
            },
          ],
          color: [
            {
              item: '#e93323',
            },
          ],
        },
        labelColor: {
          title: '活动标签',
          name: 'labelColor',
          default: [
            {
              item: '#e93323',
            },
          ],
          color: [
            {
              item: '#e93323',
            },
          ],
        },
        // 页面间距
        mbConfig: {
          title: '页面间距',
          val: 0,
          min: 0,
        },
        numConfig: {
          val: 6,
        },
      },
      navlist: [],
      imgStyle: '',
      txtColor: '',
      slider: '',
      tabCur: 0,
      list: [],
      activeColor: '',
      fontColor: '',
      labelColor: '',
      pageData: {},
      titleConfig: 0,
      titleShow: true,
      opriceShow: true,
      priceShow: true,
      couponShow: true,
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
        this.navlist = data.tabConfig.list;
        // this.imgStyle = data.imgStyle.type
        this.activeColor = data.themeColor.color[0].item;
        this.fontColor = data.fontColor.color[0].item;
        this.labelColor = data.labelColor.color[0].item;
        this.slider = data.mbConfig.val;
        this.titleConfig = data.titleConfig.type;
        this.titleShow = data.titleShow.val;
        this.opriceShow = data.opriceShow.val;
        this.priceShow = data.priceShow.val;
        this.couponShow = data.couponShow.val;
        this.tabCur = data.tabConfig.tabCur || 0;
        let productList = data.productList.list || [];
        if (productList.length) {
          this.list = productList;
        } else {
          this.list = [
            {
              image: '',
              store_name: '小米便携式蓝牙音响',
              price: '59',
              ot_price: 135,
              checkCoupon: true,
              activity: { type: '2', id: 5 },
            },
            {
              image: '',
              store_name: '小米便携式蓝牙音响',
              price: '59',
              ot_price: 135,
              checkCoupon: true,
              activity: [],
            },
          ];
        }
      }
    },
  },
};
</script>

<style scoped lang="stylus">
.home_product
    .hd_nav
        display flex
        height 65px
        padding 0 5px
        .item
            display flex
            flex-direction column
            justify-content center
            width 25%
            .title
                font-size 16px
                color #282828
                width 65px
                text-align center
            .label
                width:62px;
                height:18px;
                line-height 18px
                text-align center
                background:transparent;
                border-radius:8px;
                color #999999
                font-size 12px
            &.active
                .title
                    color #FF4444
                .label
                    color #fff
                    background:linear-gradient(270deg,rgba(255,84,0,1) 0%,rgba(255,0,0,1) 100%);
    .list-wrapper
        display flex
        flex-wrap wrap
        justify-content space-between
        .item
            width 170px
            margin-bottom 10px
            .img-box
                position relative
                width 100%
                height 173px
                img,.box
                    width 100%
                    height 100%
                    border-radius:10px 10px 0px 0px;
                .box
                    background #D8D8D8
                .label
                    position absolute
                    left 0
                    top 0
                    width:46px;
                    height:22px;
                    border-radius:10px 0px 10px 0px;
                    color #fff
                    font-size 13px
                    text-align center
                    line-height 22px
            .info
                padding 7px 10px
                background #fff
                border-radius: 0px 0px 10px 10px;
                .title
                    font-size 14px
                    color #282828
                .old-price
                    color #aaa
                    font-size 13px
                    text-decoration: line-through;
                .price
                    display flex
                    align-items center
                    .num
                        font-size 16px
                        font-weight bold
                        span
                            font-size 12px
                    .label
                        width:16px;
                        height:18px;
                        margin-left 5px
                        text-align center
                        line-height 18px
                        font-size 11px
                        &.on
                           margin-left 0;
</style>
